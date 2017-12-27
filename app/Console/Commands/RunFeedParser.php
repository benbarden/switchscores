<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Feed\Parser;
use App\Services\Feed\TitleParser;
use App\Services\FeedItemReviewService;
use App\Services\GameService;

class RunFeedParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RunFeedParser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the feed parser.';

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
        /* @var FeedItemReviewService $feedItemReviewService */
        /* @var GameService $gameService */

        $feedItems = $feedItemReviewService->getItemsToParse();

        if (!$feedItems) {
            $this->info('No items to parse. Aborting.');
            return true;
        }

        // Set up parser
        $titleParser = new TitleParser();
        $parser = new Parser($titleParser);

        foreach ($feedItems as $feedItem) {

            $siteId = $feedItem->site_id;
            $itemUrl = $feedItem->item_url;
            $itemTitle = $feedItem->item_title;

            try {

                $this->info('Processing item: '.$itemTitle);

                $parser->setSiteId($siteId);
                $parser->getTitleParser()->setTitle($itemTitle);
                $parser->parseBySiteRules();
                $parsedTitle = $parser->getTitleParser()->getTitle();

                $feedItem->parsed_title = $parsedTitle;

                // Can we find a game from this title?
                $game = $gameService->getByTitle($parsedTitle);
                if ($game) {
                    $feedItem->game_id = $game->id;
                    $parseStatus = 'Matched item to game '.$game->id;
                    $this->info($parseStatus);
                } else {
                    $parseStatus = 'Could not locate game';
                    $this->warn($parseStatus);
                }

                $feedItem->parse_status = $parseStatus;
                $feedItem->parsed = 1;

                $feedItem->save();

            } catch (\Exception $e) {
                $this->error('Got error: '.$e->getMessage().'; skipping');
            }

        }
    }
}
