<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\GameService;
use App\Services\GenreService;
use App\Services\GameGenreService;
use App\Services\EshopEuropeGameService;
use App\Services\Eshop\Europe\UpdateGameData;

use App\Construction\GameChangeHistory\Director as GameChangeHistoryDirector;
use App\Construction\GameChangeHistory\Builder as GameChangeHistoryBuilder;

class EshopEuropeUpdateGameData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EshopEuropeUpdateGameData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates data for games linked to eShop Europe data records.';

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
     * @throws \Exception
     */
    public function handle()
    {
        $this->info(' *** '.$this->signature.' ['.date('Y-m-d H:i:s').']'.' *** ');

        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */
        $genreService = resolve('Services\GenreService');
        /* @var GenreService $genreService */
        $gameGenreService = resolve('Services\GameGenreService');
        /* @var GameGenreService $gameGenreService */
        $eshopEuropeGameService = resolve('Services\EshopEuropeGameService');
        /* @var EshopEuropeGameService $eshopEuropeGameService */

        $serviceUpdateGameData = new UpdateGameData();

        $this->info('Loading data...');

        $eshopList = $eshopEuropeGameService->getAllWithLink();

        foreach ($eshopList as $eshopItem) {

            $saveChanges = false;
            $showSplitter = false;

            $fsId = $eshopItem->fs_id;
            $eshopTitle = $eshopItem->title;
            $eshopUrl = $eshopItem->url;

            $game = $gameService->getByFsId('eu', $fsId);

            if (!$game) {
                $this->error($eshopTitle.' - no game linked to fs_id: '.$fsId.'; skipping');
                continue;
            }

            if (!$eshopUrl) {
                $this->error($eshopTitle.' - no URL found for this record. Skipping');
                continue;
            }

            // GAME CORE DATA
            $gameId = $game->id;
            $gameTitle = $game->title;
            $gameReleaseDate = $game->regionReleaseDate('eu');
            $gameGenres = $gameGenreService->getByGame($gameId);

            // SETUP
            $serviceUpdateGameData->setEshopItem($eshopItem);
            $serviceUpdateGameData->setGame($game);
            $serviceUpdateGameData->setGameReleaseDate($gameReleaseDate);
            $serviceUpdateGameData->setGameGenres($gameGenres);
            $serviceUpdateGameData->resetLogMessages();

            // STORE METHOD NAMES FOR LOOPING LATER
            $updateGameDataMethods = [
                'updateNoOfPlayers',
                'updatePublisher',
                'updatePrice',
                'updateReleaseDate',
                'updateGenres',
            ];

            // UPDATES
            foreach ($updateGameDataMethods as $method) {

                call_user_func([$serviceUpdateGameData, $method]);

                if ($serviceUpdateGameData->getLogMessageError()) {

                    $this->error($serviceUpdateGameData->getLogMessageError());
                    $showSplitter = true;

                } elseif ($serviceUpdateGameData->getLogMessageWarning()) {

                    $this->warn($serviceUpdateGameData->getLogMessageWarning());
                    $showSplitter = true;

                } elseif ($serviceUpdateGameData->getLogMessageInfo()) {

                    $this->info($serviceUpdateGameData->getLogMessageInfo());
                    $showSplitter = true;

                }

                $serviceUpdateGameData->resetLogMessages();

            }

            // ***************************************************** //

            if ($saveChanges || $serviceUpdateGameData->hasGameChanged()) {

                // Recreate objects each time to avoid issues
                $gameChangeHistoryDirector = new GameChangeHistoryDirector();
                $gameChangeHistoryBuilder = new GameChangeHistoryBuilder();

                // Get original version before saving
                $gameOrig = $game->fresh();

                $game->save();

                // Game change history
                $gameChangeHistoryBuilder->setGame($game);
                $gameChangeHistoryBuilder->setGameOriginal($gameOrig);
                $gameChangeHistoryDirector->setBuilder($gameChangeHistoryBuilder);
                $gameChangeHistoryDirector->setTableNameGames();
                $gameChangeHistoryDirector->buildEshopEuropeUpdate();
                $gameChangeHistory = $gameChangeHistoryBuilder->getGameChangeHistory();
                $gameChangeHistory->save();

            }

            if ($serviceUpdateGameData->hasGameReleaseDateChanged()) {
                $gameReleaseDate = $serviceUpdateGameData->getGameReleaseDate();
                $gameReleaseDate->save();
            }

            if ($showSplitter) {
                $this->info('***********************************************************');
            }
        }
    }
}
