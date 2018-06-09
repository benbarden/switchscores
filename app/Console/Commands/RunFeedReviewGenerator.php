<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\ReviewLink;
use App\Services\FeedItemReviewService;
use App\Services\GameService;
use App\Services\ReviewLinkService;
use App\Services\ReviewSiteService;
use App\Services\ReviewStatsService;
use App\Events\ReviewLinkCreated;

class RunFeedReviewGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RunFeedReviewGenerator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the feed review generator.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info(' *** '.$this->signature.' ['.date('Y-m-d H:i:s').']'.' *** ');

        $feedItemReviewService = resolve('Services\FeedItemReviewService');
        $gameService = resolve('Services\GameService');
        $reviewLinkService = resolve('Services\ReviewLinkService');
        $reviewSiteService = resolve('Services\ReviewSiteService');
        $reviewStatsService = resolve('Services\ReviewStatsService');
        /* @var FeedItemReviewService $feedItemReviewService */
        /* @var GameService $gameService */
        /* @var ReviewLinkService $reviewLinkService */
        /* @var ReviewSiteService $reviewSiteService */
        /* @var ReviewStatsService $reviewStatsService */

        $feedItems = $feedItemReviewService->getUnprocessed();

        if (!$feedItems) {
            $this->info('No items to process. Aborting.');
            return true;
        }

        foreach ($feedItems as $feedItem) {

            try {

                $itemId = $feedItem->id;
                $this->info('Processing item: '.$itemId. ' with date: '.$feedItem->item_date);
                $processStatus = '';

                // Check the fields we need to create a review
                $gameId = $feedItem->game_id;
                $itemUrl = $feedItem->item_url;
                $siteId = $feedItem->site_id;
                $itemDate = $feedItem->item_date;
                $itemRating = $feedItem->item_rating;

                // Check for a duplicate review
                // We can do this even if we don't have all the other fields yet
                if ($gameId && $siteId) {
                    $existingReview = $reviewLinkService->getByGameAndSite($gameId, $siteId);
                    if ($existingReview) {
                        $this->warn('Existing review found for this game/site. Marking as a duplicate.');
                        $feedItem->process_status = 'Duplicate';
                        $feedItem->processed = 1;
                        $feedItem->save();
                        continue;
                    }
                }

                // Check for missing fields
                $missingFields = [];
                if (!$gameId) {
                    $missingFields[] = 'gameId';
                }
                if (!$itemUrl) {
                    $missingFields[] = 'itemUrl';
                }
                if (!$siteId) {
                    $missingFields[] = 'siteId';
                }
                if (!$itemDate) {
                    $missingFields[] = 'itemDate';
                }
                if (!$itemRating) {
                    $missingFields[] = 'itemRating';
                }

                if ($missingFields) {
                    $this->error('Unable to process due to missing field(s): '.implode($missingFields, ', '));
                    $feedItem->process_status = $processStatus;
                    $feedItem->save();
                    continue;
                }

                // Reformat the date
                $itemDateShort = date('Y-m-d', strtotime($itemDate));
                $this->info('Reformatting date: '.$itemDate.' as short date: '.$itemDateShort);

                // We're good to go - let's create the review
                $reviewSite = $reviewSiteService->find($siteId);
                $ratingNormalised = $reviewLinkService->getNormalisedRating($itemRating, $reviewSite);

                $reviewLink = $reviewLinkService->create(
                    $gameId, $siteId, $itemUrl, $itemRating, $ratingNormalised, $itemDateShort,
                    ReviewLink::TYPE_IMPORTED
                );

                // Update game review stats
                $game = $gameService->find($gameId);
                $reviewStatsService->updateGameReviewStats($game);

                // Mark feed item as processed
                $feedItem->process_status = 'Review created';
                $feedItem->processed = 1;
                $feedItem->save();

                $this->info('Successfully created review with id: '.$reviewLink->id);

                // Trigger event
                event(new ReviewLinkCreated($reviewLink));

            } catch (\Exception $e) {
                $this->error('Got error: '.$e->getMessage().'; skipping');
            }

        }

    }
}
