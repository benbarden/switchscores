<?php

namespace App\Domain\ReviewDraft;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\ReviewLink\Builder as ReviewLinkBuilder;
use App\Domain\ReviewLink\Calculations as ReviewLinkCalculations;
use App\Domain\ReviewLink\Director as ReviewLinkDirector;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;

use App\Models\Game;
use App\Models\QuickReview;
use App\Models\ReviewDraft;

class ConvertToReviewLink
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function processItem(ReviewDraft $draftItem)
    {
        $repoReviewLink = new ReviewLinkRepository();
        $calcReviewLink = new ReviewLinkCalculations();
        $repoGame = new GameRepository();
        $repoReviewSite = new ReviewSiteRepository();

        $itemId = $draftItem->id;

        $userId = $draftItem->user_id;
        $gameId = $draftItem->game_id;
        $siteId = $draftItem->site_id;
        $itemUrl = $draftItem->item_url;
        $itemDate = date('Y-m-d', strtotime($draftItem->item_date));
        $itemRating = $draftItem->item_rating;

        $this->logger->info("Processing item $itemId");

        // Skip duplicates
        $existingReview = $repoReviewLink->byGameAndSite($gameId, $siteId);
        if ($existingReview) {
            $this->logger->warning("Existing review found: Game $gameId; Site $siteId. Marking as a duplicate.");
            $draftItem->process_status = 'Duplicate';
            $draftItem->save();
            return false;
        }

        // OK to create review
        $reviewLinkBuilder = new ReviewLinkBuilder();
        $reviewLinkDirector = new ReviewLinkDirector($reviewLinkBuilder);

        $partner = $repoReviewSite->find($siteId);
        $ratingNormalised = $calcReviewLink->normaliseRating($itemRating, $partner->rating_scale);

        $params = [
            'user_id' => $userId,
            'game_id' => $gameId,
            'site_id' => $siteId,
            'url' => $itemUrl,
            'rating_original' => $itemRating,
            'rating_normalised' => $ratingNormalised,
            'review_date' => $itemDate,
        ];

        $reviewLinkDirector->buildNewImported($params);
        $reviewLinkDirector->save();
        $reviewLink = $reviewLinkDirector->getReviewLink();

        // Mark draft as processed
        $draftItem->review_link_id = $reviewLink->id;
        $draftItem->process_status = 'Review created';
        $draftItem->save();

        $reviewLinkId = $reviewLink->id;

        // Update game review stats
        $game = $repoGame->find($gameId);
        $this->updateGameReviewStats($game);

        $this->logger->info("Item $itemId: Successfully created review with id: $reviewLinkId; date: $itemDate");

        return true;
    }

    /**
     * @param Game $game
     * @return void
     * @deprecated
     */
    public function updateGameReviewStats(Game $game)
    {
        $gameId = $game->id;

        $repoReviewLink = new ReviewLinkRepository();
        $repoQuickReview = new QuickReviewRepository();
        $statsReviewLink = new ReviewLinkStats();

        $gameReviewLinks = $repoReviewLink->byGame($gameId);
        $gameQuickReviews = $repoQuickReview->byGameActive($gameId);
        $gameReviewStats = $statsReviewLink->calculateStats($gameReviewLinks, $gameQuickReviews);

        $game->review_count = $gameReviewStats[0];
        $game->rating_avg = $gameReviewStats[1];
        $game->save();
    }
}