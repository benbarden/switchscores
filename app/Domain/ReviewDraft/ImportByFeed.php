<?php

namespace App\Domain\ReviewDraft;

use Carbon\Carbon;

use App\Models\ReviewSite;
use App\Models\ReviewDraft;
use App\Models\PartnerFeedLink;

use App\Domain\Feed\Loader;
use App\Domain\Feed\Importer;

use App\Domain\Game\Repository as RepoGame;
use App\Domain\ReviewDraft\Builder as ReviewDraftBuilder;
use App\Domain\ReviewDraft\Director as ReviewDraftDirector;
use App\Domain\ReviewDraft\Repository as RepoReviewDraft;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Services\Game\TitleMatch as ServiceTitleMatch;
use App\Services\UrlService;

use App\Exceptions\Review\AlreadyImported;
use App\Exceptions\Review\FeedUrlPrefixNotMatched;
use App\Exceptions\Review\HistoricEntry;
use App\Exceptions\Review\TitleRuleNotMatched;

class ImportByFeed
{
    /**
     * @var PartnerFeedLink
     */
    private $partnerFeedLink;

    /**
     * @var ReviewSite
     */
    private $reviewSite;

    private $logger;

    /**
     * @var \App\Domain\Game\Repository
     */
    private $repoGame;

    /**
     * @var \App\Domain\ReviewSite\Repository
     */
    private $repoReviewSite;

    /**
     * @var RepoReviewDraft
     */
    private $repoReviewDraft;

    /**
     * @var UrlService
     */
    private $serviceUrl;

    public function __construct(PartnerFeedLink $partnerFeedLink, $logger)
    {
        $this->partnerFeedLink = $partnerFeedLink;
        $this->logger = $logger;

        $this->repoGame = new RepoGame();
        $this->repoReviewSite = new ReviewSiteRepository();
        $this->repoReviewDraft = new RepoReviewDraft();
        $this->serviceUrl = new UrlService();

        $siteId = $this->partnerFeedLink->site_id;
        $this->reviewSite = $this->repoReviewSite->find($siteId);
    }

    public function runImport()
    {
        $partnerFeedUrl = $this->partnerFeedLink->feed_url;
        $partnerName = $this->reviewSite->name;

        $this->logger->info(sprintf('Site %s: %s - Feed URL: %s', $this->reviewSite->id, $partnerName, $partnerFeedUrl));

        // Load the feed
        $feedLoader = new Loader($this->partnerFeedLink);
        $feedLoader->loadByUrl($partnerFeedUrl);
        $itemArray = $feedLoader->buildItemArray();

        // Process the items
        $this->processItems($itemArray);
    }

    public function processItems($itemArray)
    {
        try {

            foreach ($itemArray as $item) {

                if ($this->partnerFeedLink->isAtom()) {
                    $itemData = $this->buildFromAtom($item);
                } else {
                    $itemData = $this->buildFromRss($item);
                }
                try {
                    $this->validateItem($itemData);
                    $logInfo = sprintf('Importing: %s - %s', $itemData['item_date'], $itemData['item_url']);
                    $this->logger->info($logInfo);
                    $this->processItem($itemData);
                } catch (AlreadyImported $e) {
                    //$this->logger->info($e->getMessage());
                    continue;
                } catch (HistoricEntry $e) {
                    //$this->logger->info($e->getMessage());
                    continue;
                } catch (FeedUrlPrefixNotMatched $e) {
                    //$this->logger->info($e->getMessage());
                    continue;
                } catch (TitleRuleNotMatched $e) {
                    //$this->logger->info($e->getMessage());
                    continue;
                }

            }

        } catch (\Exception $e) {

            $this->logger->error('Got error: '.$e->getMessage());
            $this->partnerFeedLink->was_last_run_successful = 0;
            $this->partnerFeedLink->last_run_status = $e->getMessage();
            $this->partnerFeedLink->save();

        }
    }

    public function cleanUpTitle($title)
    {
        $itemTitle = $title;
        $itemTitle = str_replace('<![CDATA[', '', $itemTitle);
        $itemTitle = str_replace(']]>', '', $itemTitle);
        $itemTitle = str_replace("\r", '', $itemTitle);
        $itemTitle = str_replace("\n", '', $itemTitle);
        return $itemTitle;
    }

    public function cleanUpUrl($url)
    {
        return $this->serviceUrl->cleanReviewFeedUrl($url);
    }

    public function buildFromAtom($item)
    {
        $itemData = [];

        // Title
        $itemData['item_title'] = $item['title'];

        // URL
        $itemUrl = null;
        $feedItemLinks = $item['link'];
        foreach ($feedItemLinks as $itemLinkTemp) {
            $itemLinkTempData = $itemLinkTemp['@attributes'];
            if ($itemLinkTempData['rel'] == 'alternate') {
                $itemUrl = $itemLinkTempData['href'];
                break;
            }
        }
        if ($itemUrl != null) {
            $itemData['item_url'] = $itemUrl;
        }

        // Date
        $itemDateModel = new Carbon($item['published']);
        $itemDate = $itemDateModel->format('Y-m-d H:i:s');
        $itemData['item_date'] = $itemDate;

        return $itemData;
    }

    public function buildFromRss($item)
    {
        if ($this->partnerFeedLink->isParseAsObjects()) {

            $itemTitle = (string) $item->title;
            $itemUrl = $item->link;

            $pubDate = $item->pubDate;
            $pubDateModel = new Carbon($pubDate);

            if (property_exists($item, 'score')) {
                $itemRating = $item->score;
            } elseif (property_exists($item, 'note')) {
                $itemRating = $item->note;
            } else {
                $itemRating = null;
            }

        } else {

            $itemTitle = $this->cleanUpTitle($item['title']);
            $itemUrl = $item['link'];

            $pubDate = $item['pubDate'];
            $pubDateModel = new Carbon($pubDate);

            if (array_key_exists('score', $item)) {
                $itemRating = $item['score'];
            } elseif (array_key_exists('note', $item)) {
                $itemRating = $item['note'];
            } else {
                $itemRating = null;
            }

        }

        $itemData['item_title'] = $itemTitle;
        $itemData['item_date'] = $pubDateModel->format('Y-m-d H:i:s');
        if (!is_null($itemRating)) {
            $itemData['item_rating'] = $itemRating;
        }

        $itemData['item_url'] = $this->cleanUpUrl($itemUrl);

        return $itemData;
    }

    public function isHistoric($itemDate)
    {
        // If the review date is older than 30 days from today, it's history!
        if (date('Y-m-d', strtotime('-30 days')) > $itemDate) {
            return true;
        } else {
            return false;
        }
    }

    public function validateItem($item)
    {
        $itemTitle = $item['item_title'];
        $itemUrl = $item['item_url'];
        $itemDate = $item['item_date'];

        // Check if it's already been imported
        $dbExistingItem = $this->repoReviewDraft->getByItemUrl($itemUrl);
        if ($dbExistingItem) {
            throw new AlreadyImported('Already imported: ' . $itemUrl);
        }

        // Check that it's not a historic review
        if ($this->isHistoric($itemDate) && !$this->partnerFeedLink->allowHistoric()) {
            throw new HistoricEntry('Skipping historic review: '.$itemUrl.' - Date: '.$itemDate);
        }

        // Check if a feed URL prefix is set, and if so, compare it
        $feedUrlPrefix = $this->partnerFeedLink->feed_url_prefix;
        if ($feedUrlPrefix) {
            $fullPrefix = $this->reviewSite->website_url.$feedUrlPrefix;
            if (substr($itemUrl, 0, strlen($fullPrefix)) != $fullPrefix) {
                throw new FeedUrlPrefixNotMatched('Does not match feed URL prefix: '.$itemUrl.' - Date: '.$itemDate);
            }
        }

        // Special rules for Digitally Downloaded
        if ($this->reviewSite->name == 'Digitally Downloaded') {

            $serviceTitleMatch = new ServiceTitleMatch();

            $titleMatchRulePattern = $this->partnerFeedLink->title_match_rule_pattern;
            $titleMatchIndex = $this->partnerFeedLink->title_match_rule_index;

            if ($titleMatchRulePattern && ($titleMatchIndex != null)) {

                // New method
                $serviceTitleMatch->setMatchRule($titleMatchRulePattern);
                $serviceTitleMatch->prepareMatchRule();
                $serviceTitleMatch->setMatchIndex($titleMatchIndex);
                $parsedTitle = $serviceTitleMatch->generate($itemTitle);

                // Can we find a game from this title?
                // NB. If there's no match, we'll skip this item altogether
                if (!$parsedTitle) {
                    throw new TitleRuleNotMatched('Does not match title rule: '.$itemUrl);
                }

            }
        }
    }

    public function processItem($item)
    {
        if (array_key_exists('item_title', $item)) {
            $itemTitle = $item['item_title'];
        } else {
            throw new \Exception('Fatal error: Required mapping item_title not found.');
        }
        if (array_key_exists('item_url', $item)) {
            $itemUrl = $item['item_url'];
        } else {
            throw new \Exception('Fatal error: Required mapping item_url not found.');
        }
        if (array_key_exists('item_date', $item)) {
            $itemDate = date('Y-m-d', strtotime($item['item_date']));
        } else {
            throw new \Exception('Fatal error: Required mapping item_date not found.');
        }
        if (array_key_exists('item_rating', $item)) {
            $itemRating = $item['item_rating'];
        } else {
            $itemRating = null;
        }

        $reviewDraft = [
            'site_id' => $this->reviewSite->id,
            'item_title' => $itemTitle,
            'item_url' => $itemUrl,
            'item_date' => $itemDate,
            'item_rating' => $itemRating,
        ];

        $game = $this->repoGame->getByTitle($itemTitle);
        if ($game) {
            $reviewDraft['game_id'] = $game->id;
            $reviewDraft['parse_status'] = ReviewDraft::PARSE_STATUS_AUTO_MATCHED;
        }

        $reviewDraftBuilder = new ReviewDraftBuilder();
        $reviewDraftDirector = new ReviewDraftDirector($reviewDraftBuilder);

        $reviewDraftDirector->buildNewFeed($reviewDraft);
        $reviewDraftDirector->save();

        return $reviewDraftDirector->getReviewDraft();
    }
}