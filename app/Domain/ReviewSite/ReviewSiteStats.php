<?php

namespace App\Domain\ReviewSite;

use App\Models\ReviewSite;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;

class ReviewSiteStats
{
    private $repoReviewSite;
    private $repoReviewLink;
    private $statsReviewLink;
    private $logger;

    public function __construct(
        $logger
    )
    {
        $this->repoReviewSite = app(ReviewSiteRepository::class);
        $this->repoReviewLink = app(ReviewLinkRepository::class);
        $this->statsReviewLink = app(ReviewLinkStats::class);
        $this->logger = $logger;
    }

    public function updateSite($siteId)
    {
        $reviewSite = $this->repoReviewSite->find($siteId);
        if (!$reviewSite) {
            $errorMsg = 'Cannot find site: '.$siteId;
            if ($this->logger) {
                $this->logger->error($errorMsg);
            }
            throw new \Exception($errorMsg);
        }

        $siteId = $reviewSite->id;
        $siteName = $reviewSite->name;

        $reviewCount = $this->statsReviewLink->totalBySite($siteId);
        $reviewLatest = $this->repoReviewLink->bySiteLatest($siteId, 1);
        if (count($reviewLatest) > 0) {
            $reviewLatestItem = $reviewLatest[0];
            $latestReviewDate = $reviewLatestItem->review_date;
        } else {
            $latestReviewDate = null;
        }

        if ($reviewSite->status == ReviewSite::STATUS_ARCHIVED) {
            // Don't update archived sites
            $siteStatus = ReviewSite::STATUS_ARCHIVED;
        } else {
            if (is_null($latestReviewDate)) {
                $siteStatus = ReviewSite::STATUS_NO_RECENT_REVIEWS;
            } elseif (date('Y-m-d', strtotime('-30 days')) > $latestReviewDate) {
                $siteStatus = ReviewSite::STATUS_NO_RECENT_REVIEWS;
            } else {
                $siteStatus = ReviewSite::STATUS_ACTIVE;
            }
        }

        $padSiteName = str_pad($siteName, 30);
        $padReviewCount = str_pad($reviewCount, 8);

        if ($this->logger) {
            $this->logger->info(sprintf("Site: %s Review count: %s Latest review: %s",
                $padSiteName, $padReviewCount, $latestReviewDate));
        }

        // Update fields
        $reviewSite->status = $siteStatus;
        $reviewSite->review_count = $reviewCount;
        $reviewSite->last_review_date = $latestReviewDate;
        $reviewSite->save();

    }

    public function updateAll()
    {
        $reviewSites = $this->repoReviewSite->getAll();

        if (!$reviewSites) {
            $errorMsg = 'No review sites found. Aborting.';
            if ($this->logger) {
                $this->logger->error($errorMsg);
            }
            throw new \Exception($errorMsg);
        }

        foreach ($reviewSites as $reviewSite) {
            $this->updateSite($reviewSite->id);
        }

    }
}