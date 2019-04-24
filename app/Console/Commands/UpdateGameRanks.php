<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\GameRankUpdateService;

class UpdateGameRanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateGameRanks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes rank for games.';

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
        $serviceGameRankUpdate = resolve('Services\GameRankUpdateService');
        /* @var GameRankUpdateService $serviceGameRankUpdate */

        $this->info(' *** '.$this->signature.' ['.date('Y-m-d H:i:s').']'.' *** ');

        // *** 1. ALL-TIME RANK *** //

        \DB::statement("TRUNCATE TABLE game_rank_alltime");

        $gameRankList = \DB::select("
            SELECT g.id AS game_id, g.title, g.rating_avg, g.game_rank
            FROM games g
            WHERE review_count > 2
            ORDER BY rating_avg DESC
        ");

        $channel = env('SLACK_ALERT_CHANNEL', '');
        if ($channel) {
            \Slack::to('#'.$channel)->send('UpdateGameRanks: '.count($gameRankList).' ranked games');
        }

        $this->info('All-time rank: checking '.count($gameRankList).' games');

        $rankCounter = 1;
        $actualRank = 1;
        $lastRatingAvg = -1;
        $lastRank = -1;

        foreach ($gameRankList as $game) {

            $gameId = $game->game_id;
            $gameTitle = $game->title;
            $ratingAvg = $game->rating_avg;
            $prevRank = $game->game_rank;

            if ($lastRatingAvg == -1) {
                // First record
                $actualRank = $rankCounter;
            } elseif ($lastRatingAvg == $ratingAvg) {
                // Same as previous rank
                $actualRank = $lastRank;
            } else {
                // Go to next rank
                $actualRank = $rankCounter;
            }

            // Notify if different
            if (!$prevRank) {

                // No previous rank - notification needed
                $this->info(sprintf('Game: %s - Rating: %s - Initial rank: %s',
                    $gameTitle, $ratingAvg, $actualRank));

                // Store rank update
                $serviceGameRankUpdate->create($gameId, null, $actualRank, $ratingAvg);

            } elseif ($prevRank != $actualRank) {

                // Log all rank changes, even though we might hide some on the site

                // Rank has changed - notification needed
                $this->info(sprintf('Game: %s - Rating: %s - Previous rank: %s - New rank: %s',
                    $gameTitle, $ratingAvg, $prevRank, $actualRank));

                // Store rank update
                $serviceGameRankUpdate->create($gameId, $prevRank, $actualRank, $ratingAvg);

            } else {

                // No change. No notification needed

            }

            // Save rank to DB
            \DB::update("
                UPDATE games SET game_rank = ? WHERE id = ?
            ", array($actualRank, $gameId));

            $lastRatingAvg = $ratingAvg;
            $lastRank = $actualRank;

            // Save to all-time rank table.
            // Although we only show the Top 100, we store the whole list so we can quickly count
            // the number of ranked games.
            \DB::insert("
                INSERT INTO game_rank_alltime(game_rank, game_id, created_at, updated_at)
                VALUES(?, ?, NOW(), NOW())", array($actualRank, $gameId));

            // This is always incremented by 1
            $rankCounter++;

        }

        // *** 2. YEAR RANK *** //

        \DB::statement("TRUNCATE TABLE game_rank_year");

        $years = [2017, 2018, 2019];

        foreach ($years as $year) {

            $gameRankList = \DB::select("
                select g.id AS game_id, g.title, g.rating_avg, g.game_rank
                from games g
                join game_release_dates grd on g.id = grd.game_id
                where grd.region = 'eu'
                and grd.release_year = ?
                and g.review_count > 2
                order by rating_avg desc
            ", [$year]);

            $this->info('Year rank ['.$year.']: checking '.count($gameRankList).' games');

            $rankCounter = 1;
            $actualRank = 1;
            $lastRatingAvg = -1;
            $lastRank = -1;

            foreach ($gameRankList as $game) {

                $gameId = $game->game_id;
                $ratingAvg = $game->rating_avg;

                if ($lastRatingAvg == -1) {
                    // First record
                    $actualRank = $rankCounter;
                } elseif ($lastRatingAvg == $ratingAvg) {
                    // Same as previous rank
                    $actualRank = $lastRank;
                } else {
                    // Go to next rank
                    $actualRank = $rankCounter;
                }

                $lastRatingAvg = $ratingAvg;
                $lastRank = $actualRank;

                if ($actualRank > 100) break;

                // Save to year rank table
                \DB::insert("
                    INSERT INTO game_rank_year(release_year, game_rank, game_id, created_at, updated_at)
                    VALUES(?, ?, ?, NOW(), NOW())", array($year, $actualRank, $gameId));

                // This is always incremented by 1
                $rankCounter++;

            }
        }

    }
}
