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

        $serviceGameChangeHistory = resolve('Services\GameChangeHistoryService');
        /* @var GameChangeHistoryService $serviceGameChangeHistory */

        $serviceUpdateGameData = new UpdateGameData();

        $this->info('Loading data...');

        $eshopList = $eshopEuropeGameService->getAllWithLink();

        $nowDate = new \DateTime('now');

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
            $serviceUpdateGameData->resetLogMessages();

            // STORE METHOD NAMES FOR LOOPING LATER
            $updateGameDataMethods = [
                'updateNintendoPageUrl',
                'updateNoOfPlayers',
                'updatePublisher',
                'updatePrice',
            ];

            // UPDATES
            foreach ($updateGameDataMethods as $method) {

                call_user_func([$serviceUpdateGameData, $method]);

                if ($serviceUpdateGameData->getLogMessageError()) {

                    $this->error($serviceUpdateGameData->getLogMessageError());

                } elseif ($serviceUpdateGameData->getLogMessageWarning()) {

                    $this->warn($serviceUpdateGameData->getLogMessageWarning());

                } elseif ($serviceUpdateGameData->getLogMessageInfo()) {

                    $this->info($serviceUpdateGameData->getLogMessageInfo());

                }

                $serviceUpdateGameData->resetLogMessages();

            }

            // ***************************************************** //

            $eshopReleaseDateRaw = $eshopItem->pretty_date_s;
            $eshopGenreList = $eshopItem->pretty_game_categories_txt;

            // *** FIELD UPDATES:
            // European release date
            // Check for bad dates
            $badDatesArray = [
                'TBD',
                '2019',
                'Spring 2019',
                'January 2019',
            ];
            try {
                if (in_array($eshopReleaseDateRaw, $badDatesArray)) {
                    $isBadDate = true;
                } else {
                    $isBadDate = false;
                    $eshopReleaseDateObj = \DateTime::createFromFormat('d/m/Y', $eshopReleaseDateRaw);
                    $eshopReleaseDate = $eshopReleaseDateObj->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                $this->error('ERROR: ['.$eshopReleaseDateRaw.'] - '.$e->getMessage());
                return;
            }

            if (!$isBadDate) {

                if ($gameReleaseDate->release_date == null) {

                    // Not set
                    $this->info($gameTitle.' - no release date. '.
                        'eShop data: '.$eshopReleaseDate.' - Updating.');

                    $gameReleaseDate->release_date = $eshopReleaseDate;
                    $gameReleaseDate->upcoming_date = $eshopReleaseDate;

                    if ($eshopReleaseDateObj > $nowDate) {
                        $gameReleaseDate->is_released = 0;
                    } else {
                        $gameReleaseDate->is_released = 1;
                    }

                    $gameReleaseDate->save();
                    $showSplitter = true;

                } elseif ($gameReleaseDate->release_date != $eshopReleaseDate) {

                    // Different
                    $this->warn($gameTitle.' - different release date. '.
                        'Game data: '.$gameReleaseDate->release_date.' - '.
                        'eShop data: '.$eshopReleaseDate);

                    $showSplitter = true;

                } else {
                    // Same value, nothing to do
                }

            }


            // *** FIELD UPDATES:
            // Genres / Categories
            if ($eshopGenreList) {

                $eshopGenres = json_decode($eshopGenreList);
                $gameGenresArray = [];
                foreach ($gameGenres as $gameGenre) {
                    $gameGenresArray[] = $gameGenre->genre->genre;
                }
                //$this->info($gameTitle.' - Found '.count($eshopGenres).' genre(s) in eShop data');

                $okToAddGenres = false;
                if (count($eshopGenres) == 0) {
                    $this->info($gameTitle.' - No eShop genres. Skipping');
                    $okToAddGenres = false;
                    $showSplitter = true;
                } elseif (count($gameGenres) == 0) {
                    $this->info($gameTitle.' - No existing genres. Adding new genres.');
                    $okToAddGenres = true;
                    $showSplitter = true;
                } elseif (count($gameGenres) != count($eshopGenres)) {
                    $this->warn($gameTitle.' - Game has '.count($gameGenres).
                        ' ['.implode(',', $gameGenresArray).'] '.
                        '; eShop has '.count($eshopGenres).
                        ' ['.implode(',', $eshopGenres).'] '.
                        '. Check for differences.');
                    $okToAddGenres = false;
                    $showSplitter = true;
                } else {
                    $okToAddGenres = false;
                }

                if ($okToAddGenres) {
                    if (count($gameGenres) > 0) {
                        $gameGenreService->deleteGameGenres($gameId);
                    }
                    foreach ($eshopGenres as $eshopGenre) {
                        $genreItem = $genreService->getByGenreTitle($eshopGenre);
                        if (!$genreItem) {
                            $this->error('Genre not found: '.$genreItem.'; skipping');
                            continue;
                        }
                        $genreId = $genreItem->id;
                        $gameGenreService->create($gameId, $genreId);
                    }
                }

            } else {
                //$this->info($gameTitle.' - No genres found in eShop data. Skipping');
                //$showSplitter = true;
            }

            // *********************************************** //

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

            if ($showSplitter) {
                $this->info('***********************************************************');
            }
        }
    }
}
