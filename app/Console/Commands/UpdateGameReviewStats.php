<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateGameReviewStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateGameReviewStats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes review stats for games.';

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

        // Review count
        $reviewCountList = \DB::select("
            SELECT g.id AS game_id, g.title, g.review_count, count(rl.game_id) AS review_count_new
            FROM games g
            LEFT JOIN review_links rl ON g.id = rl.game_id
            LEFT JOIN partners p ON rl.site_id = p.id
            WHERE p.type_id = 1 AND p.status = 1
            GROUP BY g.id;
        ");

        $logger->info('Review counts: checking '.count($reviewCountList).' games');

        foreach ($reviewCountList as $item) {

            $gameId = $item->game_id;
            $gameTitle = $item->title;
            $reviewCount = $item->review_count;
            $reviewCountNew = $item->review_count_new;

            if ($reviewCount != $reviewCountNew) {

                $logger->info(sprintf('Game: %s - Previous review count: %s - New review count: %s',
                    $gameTitle, $reviewCount, $reviewCountNew));

                \DB::update("
                    UPDATE games SET review_count = ? WHERE id = ?
                ", array($reviewCountNew, $gameId));

            }

        }

        // Average rating
        $avgRatingList = \DB::select("
            SELECT g.id AS game_id, g.title, g.review_count, g.rating_avg,
            sum(rl.rating_normalised) AS rating_sum,
            round(avg(rl.rating_normalised), 2) AS rating_avg_new
            FROM games g
            LEFT JOIN review_links rl ON g.id = rl.game_id
            LEFT JOIN partners p ON rl.site_id = p.id
            WHERE p.type_id = 1 AND p.status = 1
            GROUP BY g.id;
        ");

        $logger->info('Average ratings: checking '.count($avgRatingList).' games');

        foreach ($avgRatingList as $item) {

            $gameId = $item->game_id;
            $gameTitle = $item->title;
            $reviewCount = $item->review_count;

            if ($reviewCount == 0) {

                \DB::update("
                    UPDATE games SET rating_avg = ? WHERE id = ?
                ", array(0, $gameId));

                continue;

            }

            //$ratingSum = $item->rating_sum;
            $ratingAvg = $item->rating_avg;
            $ratingAvgNew = $item->rating_avg_new;

            if ($ratingAvg != $ratingAvgNew) {

                $logger->info(sprintf('Game: %s - Previous average: %s - New average: %s',
                    $gameTitle, $ratingAvg, $ratingAvgNew));

                \DB::update("
                    UPDATE games SET rating_avg = ? WHERE id = ?
                ", array($ratingAvgNew, $gameId));

            }

        }

    }
}
