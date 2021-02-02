<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\ReviewLink;
use App\Events\ReviewLinkCreated;

use App\Traits\SwitchServices;

class RunFeedReviewGenerator extends Command
{
    use SwitchServices;

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
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();
        $serviceGame = $this->getServiceGame();
        $serviceReviewLink = $this->getServiceReviewLink();
        $serviceReviewStats = $this->getServiceReviewStats();
        $servicePartner = $this->getServicePartner();

        $feedItems = $serviceReviewFeedItem->getUnprocessed();

        if (!$feedItems) {
            $logger->info('No items to process. Aborting.');
            return true;
        }

        foreach ($feedItems as $feedItem) {

            try {

                $itemId = $feedItem->id;
                //$logger->info('Processing item: '.$itemId. ' with date: '.$feedItem->item_date);
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
                    $existingReview = $serviceReviewLink->getByGameAndSite($gameId, $siteId);
                    if ($existingReview) {
                        $logger->warning('Existing review found for this game/site. Marking as a duplicate.');
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
                    //$logger->error('Unable to process due to missing field(s): '.implode($missingFields, ', '));
                    //$feedItem->process_status = $processStatus;
                    //$feedItem->save();
                    continue;
                }

                // Reformat the date
                $itemDateShort = date('Y-m-d', strtotime($itemDate));
                //$logger->info('Reformatting date: '.$itemDate.' as short date: '.$itemDateShort);

                // We're good to go - let's create the review
                $reviewSite = $servicePartner->find($siteId);
                $ratingNormalised = $serviceReviewLink->getNormalisedRating($itemRating, $reviewSite);

                $reviewLink = $serviceReviewLink->create(
                    $gameId, $siteId, $itemUrl, $itemRating, $ratingNormalised, $itemDateShort,
                    ReviewLink::TYPE_IMPORTED
                );

                // Update game review stats
                $game = $serviceGame->find($gameId);
                $reviewLinks = $this->getServiceReviewLink()->getByGame($gameId);
                $quickReviews = $this->getServiceQuickReview()->getActiveByGame($gameId);
                $this->getServiceReviewStats()->updateGameReviewStats($game, $reviewLinks, $quickReviews);

                // Mark as parsed, in case we manually updated it
                $feedItem->parsed = 1;

                // Mark feed item as processed
                $feedItem->process_status = 'Review created';
                $feedItem->processed = 1;
                $feedItem->save();

                $logger->info("Item {$itemId}: Successfully created review with id: {$reviewLink->id}; date: {$feedItem->item_date}");

                // Trigger event
                event(new ReviewLinkCreated($reviewLink));

            } catch (\Exception $e) {
                $logger->error('Got error: '.$e->getMessage().'; skipping');
            }

        }

    }
}
