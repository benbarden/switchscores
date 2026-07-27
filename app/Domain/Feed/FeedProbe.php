<?php

namespace App\Domain\Feed;

use App\Models\PartnerFeedLink;

use App\Domain\ReviewDraft\ImportByFeed;
use App\Domain\PartnerFeedLink\TestTitleRule;


/**
 * Works out how a feed needs to be configured, from the feed itself.
 *
 * Onboarding a review site currently means choosing data_type and item_node from dropdowns
 * before anything can validate them, and both fail silently when wrong: the import simply
 * finds no items, or finds items with no score. This reads the feed once and proposes the
 * settings, with the evidence for each.
 *
 * Read-only by design. It creates nothing and writes nothing, so it is safe to point at a
 * partner's feed mid-conversation and safe to run in bulk across every feed already stored.
 *
 * Detection is a suggestion, never an answer. Everything it returns is meant to land in an
 * editable field.
 */
class FeedProbe
{
    /**
     * Item nodes the Loader can actually walk in each parse mode. Not every combination is
     * implemented, and an unsupported pair yields zero items rather than an error - so the
     * probe has to refuse those pairs itself.
     *
     * @see Loader::generateItemsArray()
     */
    const SUPPORTED_NODES_OBJECT = [
        PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM,
        PartnerFeedLink::ITEM_NODE_POST,
    ];

    const SUPPORTED_NODES_ARRAY = [
        PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM,
        PartnerFeedLink::ITEM_NODE_ITEM,
        PartnerFeedLink::ITEM_NODE_ENTRY,
    ];

    /**
     * Element names the importer reads a review score from, in the order it tries them.
     *
     * @see ImportByFeed::buildFromRss()
     */
    const SCORE_ELEMENTS = ['score', 'note'];

    /**
     * Scores are stored in review_drafts.item_rating, a decimal(4, 1), so anything finer
     * than one decimal place is silently rounded on import.
     */
    const RATING_DECIMAL_PLACES = 1;

    const MAX_SAMPLE_TITLES = 50;

    private $fetcher;

    public function __construct(FeedFetcher $fetcher = null)
    {
        $this->fetcher = $fetcher ?: new FeedFetcher();
    }

    /**
     * @param string $feedUrl
     * @return FeedProbeResult
     */
    public function probe($feedUrl)
    {
        try {
            $body = $this->fetcher->fetch($feedUrl);
        } catch (\Exception $e) {
            return (new FeedProbeResult($feedUrl))->setLoadError($e->getMessage());
        }

        return $this->probeBody($body, $feedUrl);
    }

    /**
     * The detection itself, against a body already in hand. Separate from probe() so it can
     * be tested against fixtures without a network call.
     *
     * @param string $body
     * @param string $feedUrl
     * @return FeedProbeResult
     */
    public function probeBody($body, $feedUrl = null)
    {
        $result = new FeedProbeResult($feedUrl);

        $xml = $this->parseXml($body);

        if ($xml === false) {
            return $result->setLoadError('Response is not valid XML: '.$this->firstXmlError());
        }

        $itemNode = $this->detectItemNode($xml);

        if ($itemNode === null) {
            return $result->setLoadError(
                'Could not find any items. Looked for channel > item, item, entry (Atom) and post.'
            );
        }

        $result->setDetection('item_node', $itemNode, $this->itemNodeEvidence($itemNode));

        $this->detectDataType($body, $itemNode, $result);

        $dataType = $result->getDetection('data_type');

        if ($dataType === null) {
            // Nothing further is safe to report: every remaining detection depends on being
            // able to read the items, and we have just established that we cannot.
            return $result;
        }

        $this->detectSiteDetails($xml, $result);
        $this->detectScores($body, $itemNode, $result);
        $this->detectTitles($body, $dataType, $itemNode, $result);

        return $result;
    }

    private function parseXml($body)
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $xml = simplexml_load_string($body);

        libxml_use_internal_errors($previous);

        return $xml;
    }

    private function firstXmlError()
    {
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return count($errors) > 0 ? trim($errors[0]->message) : 'no detail available';
    }

    /**
     * Atom is checked first: an Atom document has a feed root with entry children and no
     * channel, so testing for channel > item first would not misfire, but ordering by
     * specificity keeps the intent obvious.
     */
    private function detectItemNode(\SimpleXMLElement $xml)
    {
        if ($xml->getName() == 'feed' && isset($xml->entry)) {
            return PartnerFeedLink::ITEM_NODE_ENTRY;
        }

        if (isset($xml->channel->item)) {
            return PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM;
        }

        if (isset($xml->item)) {
            return PartnerFeedLink::ITEM_NODE_ITEM;
        }

        if (isset($xml->post)) {
            return PartnerFeedLink::ITEM_NODE_POST;
        }

        return null;
    }

    private function itemNodeEvidence($itemNode)
    {
        switch ($itemNode) {
            case PartnerFeedLink::ITEM_NODE_ENTRY:
                return 'Atom document: feed root with entry children.';
            case PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM:
                return 'Standard RSS: items sit under channel > item.';
            case PartnerFeedLink::ITEM_NODE_ITEM:
                return 'Items sit at the document root, with no channel wrapper.';
            case PartnerFeedLink::ITEM_NODE_POST:
                return 'Items sit at the document root as post elements.';
        }

        return null;
    }

    /**
     * Chooses the parse mode by reading the feed both ways and comparing what survives.
     *
     * This is deliberately empirical rather than rule-based. Array mode runs the document
     * through json_encode(), which discards some content, and the exact rule is easy to get
     * wrong: it is CDATA that gets lost, NOT attributes. An element written as
     * <score max="10">7.2</score> survives array mode perfectly well - the attribute is
     * dropped and the 7.2 is kept - whereas <title><![CDATA[Name]]></title> comes back as an
     * empty array. Comparing actual extraction cannot be fooled by whichever quirk applies.
     */
    private function detectDataType($body, $itemNode, FeedProbeResult $result)
    {
        $objectSupported = in_array($itemNode, self::SUPPORTED_NODES_OBJECT);
        $arraySupported = in_array($itemNode, self::SUPPORTED_NODES_ARRAY);

        if (!$objectSupported && !$arraySupported) {
            $result->addWarning(
                'This item node cannot be read in either parse mode. The feed cannot be '
                .'imported without a code change to the feed loader.',
                FeedProbeResult::SEVERITY_ERROR
            );
            return;
        }

        // Where only one mode can walk this node type, the choice is already made. Getting
        // this wrong is one of the silent failures: an unsupported pair returns zero items
        // and looks exactly like an empty feed.
        if (!$arraySupported) {
            $result->setDetection(
                'data_type',
                PartnerFeedLink::DATA_TYPE_OBJECT,
                'Only Object mode can read '.$this->itemNodeName($itemNode).' items.'
            );
            return;
        }

        if (!$objectSupported) {
            $result->setDetection(
                'data_type',
                PartnerFeedLink::DATA_TYPE_ARRAY,
                'Only Array mode can read '.$this->itemNodeName($itemNode).' items.'
            );
            return;
        }

        $objectItems = $this->loadItems($body, PartnerFeedLink::DATA_TYPE_OBJECT, $itemNode);
        $arrayItems = $this->loadItems($body, PartnerFeedLink::DATA_TYPE_ARRAY, $itemNode);

        $result->setItemCount(count($objectItems));

        if (count($objectItems) == 0) {
            $result->addWarning(
                'The feed parsed, but contains no items.',
                FeedProbeResult::SEVERITY_ERROR
            );
            return;
        }

        $lostFields = $this->fieldsLostInArrayMode($objectItems, $arrayItems);

        if (count($lostFields) > 0) {
            $result->setDetection(
                'data_type',
                PartnerFeedLink::DATA_TYPE_OBJECT,
                'Array mode loses '.implode(', ', $lostFields).' on this feed, most likely '
                .'because the value is wrapped in CDATA. Object mode reads it correctly.'
            );
            return;
        }

        $result->setDetection(
            'data_type',
            PartnerFeedLink::DATA_TYPE_ARRAY,
            'Every field the importer reads survives array mode, so the simpler mode is enough.'
        );
    }

    /**
     * Compares the fields the importer actually reads, item by item, under both modes.
     * Anything present in object mode and missing in array mode would be silently lost.
     */
    private function fieldsLostInArrayMode(array $objectItems, array $arrayItems)
    {
        $lost = [];

        $count = min(count($objectItems), count($arrayItems));

        for ($i = 0; $i < $count; $i++) {

            $objectItem = $objectItems[$i];
            $arrayItem = $arrayItems[$i];

            foreach (array_merge(['title', 'link', 'pubDate'], self::SCORE_ELEMENTS) as $field) {

                if (in_array($field, $lost)) {
                    continue;
                }

                if (!isset($objectItem->$field)) {
                    continue;
                }

                $objectValue = trim((string) $objectItem->$field);

                if ($objectValue === '') {
                    continue;
                }

                $arrayValue = array_key_exists($field, $arrayItem) ? $arrayItem[$field] : null;

                if (!is_string($arrayValue) || trim($arrayValue) === '') {
                    $lost[] = $field;
                }
            }
        }

        return $lost;
    }

    private function detectSiteDetails(\SimpleXMLElement $xml, FeedProbeResult $result)
    {
        $isAtom = $xml->getName() == 'feed';
        $channel = $isAtom ? $xml : $xml->channel;

        $channelTitle = isset($channel->title) ? trim((string) $channel->title) : '';

        if ($channelTitle !== '') {

            $result->setDetection('name', $channelTitle, 'Taken from the feed title.');
            $result->setDetection('link_title', $this->slugify($channelTitle), 'Slug of the feed title.');

            // A channel title is the name of the feed, which is often not the name of the
            // site: "Casualvania - Nintendo Switch Reviews" is a feed, the partner is
            // "Casualvania". Cheap to spot, and awkward to correct later because link_title
            // ends up in public URLs.
            if ($this->looksLikeFeedName($channelTitle)) {
                $result->addWarning(
                    'The feed title "'.$channelTitle.'" looks like the name of the feed rather '
                    .'than the name of the site. Check the site name and link title before saving - '
                    .'link title is used in public URLs.',
                    FeedProbeResult::SEVERITY_NOTE
                );
            }
        }

        $link = $this->extractChannelLink($channel, $isAtom);

        if ($link !== '') {
            $result->setDetection('website_url', $link, 'Taken from the feed link.');
        }
    }

    private function extractChannelLink(\SimpleXMLElement $channel, $isAtom)
    {
        if (!isset($channel->link)) {
            return '';
        }

        if (!$isAtom) {
            return trim((string) $channel->link);
        }

        // Atom puts the URL in an href attribute and can carry several links; the one
        // pointing at the site is rel="alternate", explicitly or by omission.
        foreach ($channel->link as $link) {
            $rel = (string) $link['rel'];
            if ($rel === '' || $rel == 'alternate') {
                return trim((string) $link['href']);
            }
        }

        return '';
    }

    /**
     * Reports whether the importer will find a score, and on what scale.
     */
    private function detectScores($body, $itemNode, FeedProbeResult $result)
    {
        $items = $this->loadItems($body, PartnerFeedLink::DATA_TYPE_OBJECT, $itemNode);

        if (count($items) == 0) {
            // Object mode cannot walk this node type; scores are reported from the array
            // side instead, where attributes are unavailable, so scale cannot be read.
            $this->detectScoresFromArray($body, $itemNode, $result);
            return;
        }

        $withScore = 0;
        $scales = [];
        $tooPrecise = [];

        foreach ($items as $item) {

            $element = $this->scoreElement($item);

            if ($element === null) {
                continue;
            }

            $withScore++;

            $max = (string) $item->$element['max'];
            if ($max !== '') {
                $scales[$max] = true;
            }

            $value = trim((string) $item->$element);
            if ($this->exceedsStoredPrecision($value)) {
                $tooPrecise[] = $value;
            }
        }

        $total = count($items);

        $this->reportScoreCoverage($withScore, $total, $body, $result);

        if (count($scales) == 1) {
            $scale = array_key_first($scales);
            $result->setDetection('rating_scale', $scale, 'From the max attribute on the score element.');
        } elseif (count($scales) > 1) {
            $result->addWarning(
                'Items disagree on the rating scale (found max = '.implode(', ', array_keys($scales))
                .'). The site takes a single rating scale, so this needs resolving with the partner.',
                FeedProbeResult::SEVERITY_WARNING
            );
        } elseif ($withScore > 0) {
            $result->addWarning(
                'Scores have no max attribute, so the rating scale cannot be read from the feed '
                .'and must be set by hand.',
                FeedProbeResult::SEVERITY_NOTE
            );
        }

        if (count($tooPrecise) > 0) {
            $result->addWarning(
                'Some scores carry more than '.self::RATING_DECIMAL_PLACES.' decimal place (e.g. '
                .$tooPrecise[0].'). Scores are stored as decimal(4, 1), so these will be rounded '
                .'on import.',
                FeedProbeResult::SEVERITY_WARNING
            );
        }
    }

    private function detectScoresFromArray($body, $itemNode, FeedProbeResult $result)
    {
        $items = $this->loadItems($body, PartnerFeedLink::DATA_TYPE_ARRAY, $itemNode);

        $withScore = 0;

        foreach ($items as $item) {
            foreach (self::SCORE_ELEMENTS as $element) {
                if (array_key_exists($element, $item) && is_string($item[$element]) && trim($item[$element]) !== '') {
                    $withScore++;
                    break;
                }
            }
        }

        $this->reportScoreCoverage($withScore, count($items), $body, $result);
    }

    private function reportScoreCoverage($withScore, $total, $body, FeedProbeResult $result)
    {
        $result->setDetection('items_with_score', $withScore, $withScore.' of '.$total.' items carry a score.');

        if ($total == 0) {
            return;
        }

        if ($withScore == 0) {

            $message = 'No score found. The importer reads a dedicated <'
                .implode('> or <', self::SCORE_ELEMENTS).'> element on each item; '
                .'reviews will import without a rating until the partner adds one.';

            $inText = $this->findScoreInText($body);

            if ($inText !== null) {
                // The Casualvania case as it stood in July 2026: the score was present, but
                // as prose inside the description, where the importer cannot reach it.
                $message .= ' A score does appear in the item text ("'.$inText.'"), so the '
                    .'partner has the number and only needs to expose it as its own element.';
            }

            $result->addWarning($message, FeedProbeResult::SEVERITY_WARNING);

            return;
        }

        if ($withScore < $total) {
            $result->addWarning(
                'Only '.$withScore.' of '.$total.' items carry a score. The rest will import '
                .'without a rating.',
                FeedProbeResult::SEVERITY_WARNING
            );
        }
    }

    /**
     * Looks for a score written as prose, for feeds that have the number but have not exposed
     * it as an element. Reported so the partner can be asked for the right thing, not parsed:
     * a per-feed regex over free text is a maintenance burden nobody wants.
     */
    private function findScoreInText($body)
    {
        $patterns = [
            '/((?:rating|score|verdict)\s*[:\-]\s*\d+(?:\.\d+)?(?:\s*\/\s*\d+)?)/i',
            '/(\d+(?:\.\d+)?\s*\/\s*10)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    private function scoreElement(\SimpleXMLElement $item)
    {
        foreach (self::SCORE_ELEMENTS as $element) {
            if (isset($item->$element) && trim((string) $item->$element) !== '') {
                return $element;
            }
        }

        return null;
    }

    private function exceedsStoredPrecision($value)
    {
        if (!str_contains($value, '.')) {
            return false;
        }

        $decimals = substr($value, strpos($value, '.') + 1);

        return strlen(rtrim($decimals, '0')) > self::RATING_DECIMAL_PLACES;
    }

    /**
     * Extracts titles through the importer's own item builders, so a title shown by the probe
     * is the title the importer would store, cleanup and all.
     */
    private function detectTitles($body, $dataType, $itemNode, FeedProbeResult $result)
    {
        $items = $this->loadItems($body, $dataType, $itemNode);

        $feedLink = $this->buildFeedLink($dataType, $itemNode);

        // Resolved rather than constructed: ImportByFeed takes the game repository, which
        // takes the cache manager. None of that is used by the item builders, but the
        // dependency chain still has to be satisfied.
        $importByFeed = app(ImportByFeed::class);
        $importByFeed->setFeedLink($feedLink);

        $titles = [];
        $undatedCount = 0;

        foreach ($items as $item) {

            try {
                $itemData = $feedLink->isAtom()
                    ? $importByFeed->buildFromAtom($item)
                    : $importByFeed->buildFromRss($item);
            } catch (\Exception $e) {
                continue;
            }

            if (array_key_exists('item_title', $itemData) && trim($itemData['item_title']) !== '') {
                $titles[] = $itemData['item_title'];
            }

            if (!array_key_exists('item_date', $itemData) || !$itemData['item_date']) {
                $undatedCount++;
            }

            if (count($titles) >= self::MAX_SAMPLE_TITLES) {
                break;
            }
        }

        $result->setSampleTitles($titles);

        if (count($titles) == 0) {
            $result->addWarning(
                'No item titles could be read, so no title rule can be suggested.',
                FeedProbeResult::SEVERITY_ERROR
            );
            return;
        }

        if ($undatedCount > 0) {
            $result->addWarning(
                $undatedCount.' item(s) have no usable date. The importer needs one per item.',
                FeedProbeResult::SEVERITY_WARNING
            );
        }

        $suggestion = (new TestTitleRule())->suggestRule($titles);

        if ($suggestion['pattern'] === null) {
            $result->addWarning(
                'The titles have no consistent wrapper, so no title rule could be suggested. '
                .'These titles may match games directly at import, or may need matching by hand.',
                FeedProbeResult::SEVERITY_NOTE
            );
            return;
        }

        $matched = $this->countPatternMatches($suggestion, $titles);

        $result->setDetection(
            'title_match_rule_pattern',
            $suggestion['pattern'],
            'Derived from what all '.count($titles).' sampled titles have in common; matches '
            .$matched.' of them.'
        );
        $result->setDetection('title_match_rule_index', $suggestion['index']);

        if ($matched < count($titles)) {
            $result->addWarning(
                'The suggested title rule only matches '.$matched.' of '.count($titles)
                .' titles. Refine it on the title rule tester once the feed link is saved.',
                FeedProbeResult::SEVERITY_NOTE
            );
        }
    }

    /**
     * Counts pattern matches only. Whether a parsed title finds a game is a separate
     * question, answered by the title rule tester against the games table - the probe stays
     * out of the database so it can be run anywhere, including in bulk.
     */
    private function countPatternMatches(array $suggestion, array $titles)
    {
        $matched = 0;

        foreach ($titles as $title) {
            if (@preg_match('/^'.$suggestion['pattern'].'$/', $title) === 1) {
                $matched++;
            }
        }

        return $matched;
    }

    private function loadItems($body, $dataType, $itemNode)
    {
        try {
            $loader = new Loader($this->buildFeedLink($dataType, $itemNode));
            $loader->loadFromBody($body);
            return $loader->buildItemArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * An unsaved feed link, purely to carry the three settings the Loader and the item
     * builders read. Nothing is persisted: the probe answers "what would this feed need?"
     * without committing to it.
     */
    private function buildFeedLink($dataType, $itemNode)
    {
        $feedLink = new PartnerFeedLink();
        $feedLink->data_type = $dataType;
        $feedLink->item_node = $itemNode;

        return $feedLink;
    }

    private function itemNodeName($itemNode)
    {
        $feedLink = new PartnerFeedLink();
        $feedLink->item_node = $itemNode;

        return $feedLink->getItemNodeDesc();
    }

    private function looksLikeFeedName($title)
    {
        return (bool) preg_match('/\b(feed|rss|reviews?|switch|nintendo)\b/i', $title);
    }

    private function slugify($value)
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim($slug, '-');
    }
}
