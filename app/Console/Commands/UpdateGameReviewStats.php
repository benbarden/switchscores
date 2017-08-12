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
        // Review count
        $this->info('Review counts');

        $reviewCountList = \DB::select("
            SELECT g.id AS game_id, g.title, count(rl.game_id) AS review_count
            FROM games g
            LEFT JOIN review_links rl ON g.id = rl.game_id
            LEFT JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rs.active = 'Y'
            GROUP BY g.id;
        ");

        $this->info('Updating '.count($reviewCountList).' games');

        foreach ($reviewCountList as $item) {
            $gameId = $item->game_id;
            $reviewCount = $item->review_count;
            \DB::update("
                UPDATE games SET review_count = ? WHERE id = ?
            ", array($reviewCount, $gameId));
        }

        // Average rating
        $this->info('Average ratings');

        $avgRatingList = \DB::select("
            SELECT g.id AS game_id, g.title,
            count(rl.game_id) AS review_count,
            sum(rl.rating_normalised) AS rating_sum,
            avg(rl.rating_normalised) AS rating_avg
            FROM games g
            LEFT JOIN review_links rl ON g.id = rl.game_id
            LEFT JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rs.active = 'Y'
            GROUP BY g.id;
        ");

        $this->info('Updating '.count($avgRatingList).' games');

        foreach ($avgRatingList as $item) {
            $gameId = $item->game_id;
            $reviewCount = $item->review_count;
            if ($reviewCount == 0) continue;
            //$ratingSum = $item->rating_sum;
            $ratingAvg = $item->rating_avg;
            \DB::update("
                UPDATE games SET rating_avg = ? WHERE id = ?
            ", array($ratingAvg, $gameId));
        }

    }
}
