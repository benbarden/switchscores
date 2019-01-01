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

        $gameRankList = \DB::select("
            SELECT g.id AS game_id, g.title, g.rating_avg, g.game_rank
            FROM games g
            WHERE review_count > 2
            ORDER BY rating_avg DESC
        ");

        $this->info('Checking '.count($gameRankList).' games');

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

            //} elseif (abs($prevRank - $actualRank) > 10) {

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

            // This is always incremented by 1
            $rankCounter++;

        }

    }
}
