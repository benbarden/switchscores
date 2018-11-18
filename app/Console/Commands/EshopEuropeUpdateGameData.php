<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\GameService;
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
            $gameReleaseDate = $game->regionReleaseDate('eu');

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
            // European release date
            $eshopReleaseDateObj = \DateTime::createFromFormat('d/m/Y', $eshopReleaseDateRaw);
            $eshopReleaseDate = $eshopReleaseDateObj->format('Y-m-d');

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

            if ($saveChanges) {
                $game->save();
            }

            if ($showSplitter) {
                $this->info('***********************************************************');
            }
        }
    }
}
