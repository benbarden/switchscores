<?php

namespace App\Console\Commands\Partner;

use App\Models\PartnerFeedLink;

use App\Domain\Feed\FeedFetcher;
use App\Domain\Feed\FeedProbe;
use App\Domain\Feed\Loader;

use Illuminate\Console\Command;

/**
 * Runs the feed probe against feed links that already exist, and reports whether each one's
 * stored settings actually work.
 *
 * Two purposes. First, it measures the probe against known-correct configuration before the
 * probe is trusted on a new partner: detection rules look obviously right and are routinely
 * wrong, and there is a ready-made sample of real feeds sitting in the table. Second, it
 * finds live feeds that are misconfigured, which is not hypothetical - a previous pass over
 * this area turned up seven feeds with silently broken title rules that nobody suspected.
 *
 * The question asked of each feed is "do the stored settings read this feed correctly?",
 * NOT "do the stored settings match what the probe would have chosen". Where more than one
 * parse mode works, a difference of opinion is not a fault, and reporting it as one would
 * bury the real problems.
 *
 * Writes nothing, ever.
 */
class ProbeExistingFeedLinks extends Command
{
    protected $signature = 'PartnerProbeExistingFeedLinks
                            {--feed-link= : Check a single feed link by id}
                            {--include-inactive : Also check feeds that are not Live}
                            {--show-ok : List feeds that passed, not just the problems}';

    protected $description = 'Probes stored feed links and reports whether their saved settings read the feed correctly. Read-only.';

    const RESULT_OK = 'ok';
    const RESULT_PROBLEM = 'problem';
    const RESULT_UNREACHABLE = 'unreachable';

    public function handle()
    {
        $feedLinks = $this->getFeedLinks();

        if ($feedLinks->isEmpty()) {
            $this->error('No feed links to check.');
            return 1;
        }

        $this->line('Checking '.$feedLinks->count().' feed link(s). Nothing is written.');
        $this->newLine();

        $counts = [self::RESULT_OK => 0, self::RESULT_PROBLEM => 0, self::RESULT_UNREACHABLE => 0];
        $unreachable = [];

        $fetcher = new FeedFetcher();
        $probe = new FeedProbe();

        foreach ($feedLinks as $feedLink) {

            $label = $this->label($feedLink);

            try {
                $body = $fetcher->fetch($feedLink->feed_url);
            } catch (\Exception $e) {
                // Excluded from the accuracy figure rather than counted as a disagreement.
                // A feed that cannot be fetched says nothing about whether detection works,
                // and several stored feeds are known to be dead or behind a bot challenge.
                $counts[self::RESULT_UNREACHABLE]++;
                $unreachable[] = $label.' - '.$this->shorten($e->getMessage());
                continue;
            }

            $check = $this->checkFeedLink($feedLink, $body, $probe);

            if ($check['status'] == self::RESULT_UNREACHABLE) {
                $counts[self::RESULT_UNREACHABLE]++;
                $unreachable[] = $label.' - '.$this->shorten($check['reason']);
                continue;
            }

            $findings = $check['findings'];

            if (count($findings) == 0) {
                $counts[self::RESULT_OK]++;
                if ($this->option('show-ok')) {
                    $this->info('OK       '.$label);
                }
                continue;
            }

            $counts[self::RESULT_PROBLEM]++;
            $this->warn('PROBLEM  '.$label);
            foreach ($findings as $finding) {
                $this->line('           - '.$finding);
            }
        }

        $this->reportUnreachable($unreachable);
        $this->reportSummary($counts);

        return 0;
    }

    /**
     * @return array{status: string, findings: array, reason: ?string}
     */
    private function checkFeedLink(PartnerFeedLink $feedLink, $body, FeedProbe $probe)
    {
        $findings = [];

        $storedItems = $this->loadWithStoredSettings($feedLink, $body);

        $result = $probe->probeBody($body, $feedLink->feed_url);

        if ($result->hasLoadError()) {
            // A 200 response carrying something that is not a feed - typically a site that
            // has moved or now serves an error page as HTML. Counted as unreachable, not as
            // a settings problem: no parse mode reads a page that is not a feed, so it says
            // nothing about whether the stored configuration is right, and counting it as a
            // failure would make the pass rate a measure of how many partners are still
            // trading rather than of whether detection works.
            return [
                'status' => self::RESULT_UNREACHABLE,
                'findings' => [],
                'reason' => 'Loaded, but is not a usable feed: '.$result->getLoadError(),
            ];
        }

        // The headline failure. An unsupported or wrong item node returns an empty array
        // rather than raising anything, so this is exactly the silent case.
        if (count($storedItems) == 0) {
            $findings[] = 'Stored settings ('.$feedLink->getDataTypeDesc().' / '
                .$feedLink->getItemNodeDesc().') read ZERO items from this feed.';
        }

        $detectedNode = $result->getDetection('item_node');

        if ($detectedNode !== null && $detectedNode != $feedLink->item_node) {
            $findings[] = 'Item node stored as '.$feedLink->getItemNodeDesc()
                .', feed looks like '.$this->describeItemNode($detectedNode).'.';
        }

        $findings = array_merge($findings, $this->checkParseMode($feedLink, $body, $storedItems));

        return [
            'status' => count($findings) > 0 ? self::RESULT_PROBLEM : self::RESULT_OK,
            'findings' => $findings,
            'reason' => null,
        ];
    }

    /**
     * Reports a parse mode problem only where the stored mode actually loses something.
     *
     * Where both modes read the feed correctly the stored choice is fine whichever it is,
     * so no finding is raised. This is the difference between a useful report and a wall of
     * noise: most stored feeds sit on Array mode, and a probe that simply preferred Object
     * would flag nearly all of them.
     */
    private function checkParseMode(PartnerFeedLink $feedLink, $body, array $storedItems)
    {
        $otherType = $feedLink->isParseAsObjects()
            ? PartnerFeedLink::DATA_TYPE_ARRAY
            : PartnerFeedLink::DATA_TYPE_OBJECT;

        $otherItems = $this->loadItems($body, $otherType, $feedLink->item_node);

        if (count($otherItems) > count($storedItems)) {
            return ['Stored parse mode reads '.count($storedItems).' items; the other mode reads '
                .count($otherItems).'.'];
        }

        if (count($storedItems) == 0 || count($otherItems) == 0) {
            return [];
        }

        $lost = $this->fieldsLostUnderStoredSettings($storedItems, $otherItems, $feedLink);

        if (count($lost) > 0) {
            return ['Stored parse mode ('.$feedLink->getDataTypeDesc().') loses '
                .implode(', ', $lost).' - most likely CDATA. The other mode reads it.'];
        }

        return [];
    }

    /**
     * Compares the fields the importer reads, under the stored settings against the other
     * parse mode, and returns anything the stored settings drop.
     */
    private function fieldsLostUnderStoredSettings(array $storedItems, array $otherItems, PartnerFeedLink $feedLink)
    {
        $lost = [];

        $storedIsObject = $feedLink->isParseAsObjects();

        $count = min(count($storedItems), count($otherItems));

        for ($i = 0; $i < $count; $i++) {

            foreach (array_merge(['title', 'link', 'pubDate'], FeedProbe::SCORE_ELEMENTS) as $field) {

                if (in_array($field, $lost)) {
                    continue;
                }

                $storedValue = $this->fieldValue($storedItems[$i], $field, $storedIsObject);
                $otherValue = $this->fieldValue($otherItems[$i], $field, !$storedIsObject);

                if ($otherValue !== '' && $storedValue === '') {
                    $lost[] = $field;
                }
            }
        }

        return $lost;
    }

    private function fieldValue($item, $field, $isObject)
    {
        if ($isObject) {
            return isset($item->$field) ? trim((string) $item->$field) : '';
        }

        if (!is_array($item) || !array_key_exists($field, $item) || !is_string($item[$field])) {
            return '';
        }

        return trim($item[$field]);
    }

    private function loadWithStoredSettings(PartnerFeedLink $feedLink, $body)
    {
        return $this->loadItems($body, $feedLink->data_type, $feedLink->item_node);
    }

    private function loadItems($body, $dataType, $itemNode)
    {
        $probeLink = new PartnerFeedLink();
        $probeLink->data_type = $dataType;
        $probeLink->item_node = $itemNode;

        try {
            $loader = new Loader($probeLink);
            $loader->loadFromBody($body);
            return $loader->buildItemArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getFeedLinks()
    {
        $query = PartnerFeedLink::query();

        if ($this->option('feed-link')) {
            return $query->where('id', $this->option('feed-link'))->get();
        }

        if (!$this->option('include-inactive')) {
            $query->where('feed_status', PartnerFeedLink::FEED_STATUS_LIVE);
        }

        return $query->orderBy('id')->get();
    }

    private function label(PartnerFeedLink $feedLink)
    {
        $site = $feedLink->site;

        return sprintf(
            'Feed %d (%s) %s',
            $feedLink->id,
            $site ? $site->name : 'unknown site',
            $this->shorten($feedLink->feed_url, 60)
        );
    }

    private function describeItemNode($itemNode)
    {
        $feedLink = new PartnerFeedLink();
        $feedLink->item_node = $itemNode;

        return $feedLink->getItemNodeDesc();
    }

    private function shorten($value, $length = 90)
    {
        $value = trim(preg_replace('/\s+/', ' ', $value));

        return strlen($value) > $length ? substr($value, 0, $length).'...' : $value;
    }

    private function reportUnreachable(array $unreachable)
    {
        if (count($unreachable) == 0) {
            return;
        }

        $this->newLine();
        $this->line('Could not be reached (excluded from the pass rate):');
        foreach ($unreachable as $line) {
            $this->line('  '.$line);
        }
    }

    private function reportSummary(array $counts)
    {
        $checked = $counts[self::RESULT_OK] + $counts[self::RESULT_PROBLEM];

        $this->newLine();
        $this->line('OK:          '.$counts[self::RESULT_OK]);
        $this->line('Problems:    '.$counts[self::RESULT_PROBLEM]);
        $this->line('Unreachable: '.$counts[self::RESULT_UNREACHABLE]);

        if ($checked > 0) {
            $this->newLine();
            $this->line(sprintf(
                'Stored settings verified correct on %d of %d reachable feeds (%d%%).',
                $counts[self::RESULT_OK],
                $checked,
                round(($counts[self::RESULT_OK] / $checked) * 100)
            ));
        }
    }
}
