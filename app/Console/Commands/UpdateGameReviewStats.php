<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;

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

    private $oldGameData = [];

    private $newGameData = [];

    public function storeGameData($game, $mode)
    {
        $data = [
            'id' => $game->id,
            'title' => $game->title,
            'rating_avg' => $game->rating_avg,
            'review_count' => $game->review_count,
        ];

        if ($mode == 'old') {
            $this->oldGameData = $data;
        } elseif ($mode == 'new') {
            $this->newGameData = $data;
        }
    }

    public function logGameInfo($logger)
    {
        if ($this->oldGameData != $this->newGameData) {

            $logger->info(sprintf('OLD DATA %s - %s - %s - %s', $this->oldGameData['id'], $this->oldGameData['title'], $this->oldGameData['rating_avg'], $this->oldGameData['review_count']));

            $logger->info(sprintf('NEW DATA %s - %s - %s - %s', $this->newGameData['id'], $this->newGameData['title'], $this->newGameData['rating_avg'], $this->newGameData['review_count']));

            $logger->info('**************************************************');

        }
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

        $repoGameLists = new GameListsRepository();
        $statsReviewLink = new ReviewLinkStats();

        $gameList = $repoGameLists->allGames();

        foreach ($gameList as $game) {

            $gameId = $game->id;

            $this->storeGameData($game, 'old');

            $statsReviewLink->updateStats($game);

            $this->storeGameData($game, 'new');

            $this->logGameInfo($logger);

        }

        // Cleanup
        $logger->info('Cleanup: zeroing rating average for games with no reviews');
        DB::update("UPDATE games SET rating_avg = NULL WHERE review_count = 0");
    }
}
