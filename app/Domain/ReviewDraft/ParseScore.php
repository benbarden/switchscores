<?php

namespace App\Domain\ReviewDraft;

use App\Models\ReviewDraft;
use App\Models\ReviewSite;

use App\Domain\Scraper\Score as ScraperScore;

use App\Domain\GameTitleMatch\MatchRule;

use App\Domain\GameTitleHash\Repository as RepoGameTitleMatch;

class ParseScore
{
    private $logger;

    public function __construct($logger = null)
    {
        if ($logger) $this->logger = $logger;
    }

    public function logInfo($detail)
    {
        if ($this->logger) $this->logger->info($detail);
    }

    public function logError($detail)
    {
        if ($this->logger) $this->logger->error($detail);
    }

    public function parse(ReviewDraft $reviewDraft)
    {
        $siteId = $reviewDraft->site_id;
        $itemUrl = $reviewDraft->item_url;
        $itemTitle = $reviewDraft->item_title;

        if (!$reviewDraft->site) {
            $this->logError('Cannot find review site! ['.$siteId.']');
            return;
        }

        if ($reviewDraft->site->review_import_method == ReviewSite::REVIEW_IMPORT_BY_SCRAPER) {
            //$logger->info('Ignoring scraper items');
            return;
        }

        $scraperScore = new ScraperScore();

        $score = null;

        try {

            switch ($siteId) {

                case ReviewSite::SITE_GOD_IS_A_GEEK:
                    $scraperScore->crawlPage($itemUrl);
                    $score = $scraperScore->divItemPropRatingValueWithChildren();
                    break;

                case ReviewSite::SITE_PURE_NINTENDO:
                    $scraperScore->crawlPage($itemUrl);
                    $score = $scraperScore->spanItemPropRatingValueNoChildren();
                    break;

                case ReviewSite::SITE_NINTENPEDIA:
                    $scraperScore->crawlPage($itemUrl);
                    $score = $scraperScore->customNintenpedia();
                    break;

                case ReviewSite::SITE_HEY_POOR_PLAYER:
                    $scraperScore->crawlPage($itemUrl);
                    $score = $scraperScore->customHeyPoorPlayer();
                    break;

                case ReviewSite::SITE_SWITCHABOO:
                    $scraperScore->crawlPage($itemUrl);
                    $score = $scraperScore->customSwitchaboo();
                    break;

            }

        } catch (\Exception $e) {
            $this->logError('Got error: '.$e->getMessage());
            return;
        }

        if ($score != null) {
            $this->logInfo('Got score: ['.$score.'] for URL: '.$itemUrl);
            $reviewDraft->item_rating = $score;
            $reviewDraft->save();
        }

    }
}