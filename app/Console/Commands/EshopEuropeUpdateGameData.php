<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\GameService;
use App\Services\GenreService;
use App\Services\GameGenreService;
use App\Services\EshopEuropeGameService;

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
     *
     * @return mixed
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

        $this->info('Loading data...');

        $eshopList = $eshopEuropeGameService->getAllWithLink();

        $nowDate = new \DateTime('now');

        foreach ($eshopList as $eshopItem) {

            $saveChanges = false;
            $showSplitter = false;

            $fsId = $eshopItem->fs_id;
            $eshopTitle = $eshopItem->title;
            $eshopUrl = $eshopItem->url;
            $eshopPlayersFrom = $eshopItem->players_from;
            $eshopPlayersTo = $eshopItem->players_to;
            $eshopPublisher = $eshopItem->publisher;
            $eshopReleaseDateRaw = $eshopItem->pretty_date_s;
            $eshopGenreList = $eshopItem->pretty_game_categories_txt;
            $eshopPriceLowest = $eshopItem->price_lowest_f;
            $eshopPriceDiscount = $eshopItem->price_discount_percentage_f;

            if (strtoupper($eshopPublisher) == $eshopPublisher) {
                // It's all uppercase, so make it title case
                $eshopPublisher = ucwords(strtolower($eshopPublisher));
            } else {
                // Leave it alone
            }

            if (!$eshopUrl) {
                $this->error($eshopTitle.' - no URL found for this record. Skipping');
                continue;
            }

            $game = $gameService->getByFsId('eu', $fsId);
            $gameId = $game->id;
            $gameReleaseDate = $game->regionReleaseDate('eu');
            $gameGenres = $gameGenreService->getByGame($gameId);

            if (!$game) {
                $this->error($eshopTitle.' - no game linked to fs_id: '.$fsId.'; skipping');
                continue;
            }

            $gameTitle = $game->title;

            // *** FIELD UPDATES:
            // Nintendo page URL
            if ($game->nintendo_page_url == null) {
                // No URL set, so let's update it
                $this->info($gameTitle.' - no existing nintendo_page_url. Updating.');
                $game->nintendo_page_url = $eshopUrl;
                $saveChanges = true;
                $showSplitter = true;
            } elseif ($game->nintendo_page_url != $eshopUrl) {
                // URL set to something else
                //$this->warn($gameTitle.' - No change made. Game URL already set to: '.$game->nintendo_page_url.' - eShop record has: '.$eshopUrl);
            } else {
                // It's the same, so nothing to do
                //$this->warn($gameTitle.' - URL set and matches eShop data. Nothing to do.');
            }

            // *** FIELD UPDATES:
            // No of players
            if (!$eshopPlayersFrom) {
                $eshopPlayersFrom = "1";
            }
            if ($eshopPlayersTo == 1) {
                $expectedPlayers = "1";
            } elseif (($eshopPlayersTo == "") || ($eshopPlayersTo == null)) {
                $expectedPlayers = "";
            } else {
                $expectedPlayers = $eshopPlayersFrom."-".$eshopPlayersTo;
            }

            if ($game->players == null) {
                // Not set, so let's update it
                $this->info($gameTitle.' - no player info. '.
                    'Expected: '.$expectedPlayers.' - Updating.');
                $game->players = $expectedPlayers;
                $saveChanges = true;
                $showSplitter = true;
            } elseif ($game->players != $expectedPlayers) {
                // Different
                $this->warn($gameTitle.' - different player info. '.
                    'Game data: '.$game->players.' - '.
                    'Expected: '.$expectedPlayers);
                $showSplitter = true;
            } else {
                // Same value, nothing to do
            }

            // *** FIELD UPDATES:
            // Publisher
            if ($game->publisher == null) {
                // Not set, so let's update it
                $this->info($gameTitle.' - no publisher. '.
                    'Expected: '.$eshopPublisher.' - Updating.');
                $game->publisher = $eshopPublisher;
                $saveChanges = true;
                $showSplitter = true;
            } elseif ($game->publisher != $eshopPublisher) {
                // Different
                $this->warn($gameTitle.' - different publisher. '.
                    'Game data: '.$game->publisher.' - '.
                    'Expected: '.$eshopPublisher);
                $showSplitter = true;
            } else {
                // Same value, nothing to do
            }

            // *** FIELD UPDATES:
            // Price
            if ($eshopPriceLowest < 0) {

                // Skip negative prices. This is an error in the API!
                $this->error($gameTitle.' - Price is negative - skipping. '.
                    'Price: '.$eshopPriceLowest);
                $showSplitter = true;

            } elseif ($eshopPriceDiscount != '0.0') {

                // Skip discounts. For most games, we'll do this silently so as to save log noise.
                // If there's no price set, we'll mention it.
                if ($game->price_eshop == null) {
                    $this->info($gameTitle.' - Price is discounted - skipping. '.
                        'Price: '.$eshopPriceLowest.'; Discount: '.$eshopPriceDiscount);
                    $showSplitter = true;
                }

            } elseif ($game->price_eshop == null) {

                // Not set, so let's update it
                $this->info($gameTitle.' - no price set. '.
                    'Expected: '.$eshopPriceLowest.' - Updating.');
                $game->price_eshop = $eshopPriceLowest;
                $saveChanges = true;
                $showSplitter = true;

            } elseif ($game->price_eshop != $eshopPriceLowest) {

                // Different
                $this->warn($gameTitle.' - different price. '.
                    'Game data: '.$game->price_eshop.' - '.
                    'Expected: '.$eshopPriceLowest);

                $showSplitter = true;

            } else {

                // Same value, nothing to do

            }

            // *** FIELD UPDATES:
            // European release date
            // Check for bad dates
            if (in_array($eshopReleaseDateRaw, ['TBD'])) {
                $isBadDate = true;
            } else {
                $isBadDate = false;
                $eshopReleaseDateObj = \DateTime::createFromFormat('d/m/Y', $eshopReleaseDateRaw);
                $eshopReleaseDate = $eshopReleaseDateObj->format('Y-m-d');
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
                    //$this->info($gameTitle.' - Game and eShop have same number of genres. Check for differences.');
                    $okToAddGenres = false;
                    //$showSplitter = true;

                    // Same number of genres, but do they match?
                    /*
                    $genreListMatchCount = 0;
                    foreach ($gameGenres as $gameGenre) {
                        $gg = $gameGenre->genre;
                        foreach ($eshopGenres as $eshopGenre) {
                            $eg = $eshopGenre;
                            if ($gg == $eg) {
                                $genreListMatchCount++;
                                break;
                            }
                        }
                    }
                    if ($genreListMatchCount == count($gameGenres)) {
                        $this->info('Genre lists match - No update necessary. Skipping');
                        $okToAddGenres = false;
                        $showSplitter = true;
                    }
                    */
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

            if ($saveChanges) {
                $game->save();
            }

            if ($showSplitter) {
                $this->info('***********************************************************');
            }
        }
    }
}
