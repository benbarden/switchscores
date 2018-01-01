<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $this->info(' *** '.$this->signature.' ['.date('Y-m-d H:i:s').']'.' *** ');

        // Review count
        $this->info('Review counts');

        $reviewCountList = \DB::select("
            SELECT g.id AS game_id, g.title, g.review_count, count(rl.game_id) AS review_count_new
            FROM games g
            LEFT JOIN review_links rl ON g.id = rl.game_id
            LEFT JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rs.active = 'Y'
            GROUP BY g.id;
        ");

        $this->info('Checking '.count($reviewCountList).' games');

        foreach ($reviewCountList as $item) {

            $gameId = $item->game_id;
            $gameTitle = $item->title;
            $reviewCount = $item->review_count;
            $reviewCountNew = $item->review_count_new;

            if ($reviewCount != $reviewCountNew) {

                $this->info(sprintf('Game: %s - Previous review count: %s - New review count: %s',
                    $gameTitle, $reviewCount, $reviewCountNew));

                \DB::update("
                    UPDATE games SET review_count = ? WHERE id = ?
                ", array($reviewCountNew, $gameId));

            }

        }

        // Average rating
        $this->info('Average ratings');

        $avgRatingList = \DB::select("
            SELECT g.id AS game_id, g.title, g.review_count, g.rating_avg,
            sum(rl.rating_normalised) AS rating_sum,
            round(avg(rl.rating_normalised), 2) AS rating_avg_new
            FROM games g
            LEFT JOIN review_links rl ON g.id = rl.game_id
            LEFT JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rs.active = 'Y'
            GROUP BY g.id;
        ");

        $this->info('Checking '.count($avgRatingList).' games');

        foreach ($avgRatingList as $item) {

            $gameId = $item->game_id;
            $gameTitle = $item->title;
            $reviewCount = $item->review_count;

            if ($reviewCount == 0) continue;

            //$ratingSum = $item->rating_sum;
            $ratingAvg = $item->rating_avg;
            $ratingAvgNew = $item->rating_avg_new;

            if ($ratingAvg != $ratingAvgNew) {

                $this->info(sprintf('Game: %s - Previous average: %s - New average: %s',
                    $gameTitle, $ratingAvg, $ratingAvgNew));

                \DB::update("
                    UPDATE games SET rating_avg = ? WHERE id = ?
                ", array($ratingAvgNew, $gameId));

            }

        }

    }
}
