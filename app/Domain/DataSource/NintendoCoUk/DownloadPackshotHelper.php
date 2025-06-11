<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Domain\GameLists\Repository as RepoGameLists;
use App\Domain\Scraper\NintendoCoUkPackshot;
use App\Services\DataSources\NintendoCoUk\Images;
use App\Models\Game;

class DownloadPackshotHelper
{
    private $repoGameLists;

    private $gameList;

    private $logger;

    public function __construct($logger = null)
    {
        $this->repoGameLists = new RepoGameLists();

        if ($logger) {
            $this->logger = $logger;
        } else {
            $this->logger = null;
        }
    }

    public function downloadAllWithDataSourceId()
    {
        $this->gameList = $this->repoGameLists->anyWithNintendoCoUkId();

        if ($this->logger) {
            $this->logger->info('Found ' . count($this->gameList) . ' item(s)');
        }

        foreach ($this->gameList as $gameItem) {

            $this->downloadForGame($gameItem);

        }
    }

    public function downloadAllWithOverrideUrl()
    {
        $this->gameList = $this->repoGameLists->anyWithStoreOverride();

        if ($this->logger) {
            $this->logger->info('Found ' . count($this->gameList) . ' item(s)');
        }

        foreach ($this->gameList as $gameItem) {

            $this->downloadForGame($gameItem);

        }
    }

    public function downloadForGame(Game $game)
    {
        $itemTitle = $game->title;
        $gameId = $game->id;

        if ($this->isEligibleForDownload($game)) {
            if ($this->logger) {
                $this->logger->info('');
                $this->logger->info('Game is eligible for image download: '.$itemTitle.' ['.$gameId.']');
            }
        } else {
            return;
        }

        $packshotBuilder = new PackshotUrlBuilder();

        if ($this->hasValidDataSourceItem($game)) {

            // use DS item data
            $dsItem = $game->dspNintendoCoUk()->first();
            /*
            if ($this->logger) {
                $this->logger->info('Downloading using data source item...');
            }

            $dsItem = $game->dspNintendoCoUk()->first();
            $downloadByDataSource = new DownloadByDataSource($game, $dsItem);
            $downloadByDataSource->download();
            */

            $storeUrl = 'https://www.nintendo.com/'.$dsItem->url;

        } else {

            $dsItem = null;
            $storeUrl = $game->nintendo_store_url_override;

        }

        if ($storeUrl) {

            // use scraper
            if ($this->logger) {
                $this->logger->info('Downloading using scraper...');
            }

            $scraper = new NintendoCoUkPackshot();
            //$storeUrl = $game->nintendo_store_url_override;
            $squareUrlOverride = $game->packshot_square_url_override;

            try {
                $scraper->crawlPage($storeUrl);
                if ($dsItem) {
                    $squareUrl = $dsItem->image_square;
                } else {
                    $squareUrl = $scraper->getSquareUrl();
                }
                $headerUrl = $scraper->getHeaderUrl();
                // If we have an override, the generated URL probably errored.
                // In which case, let's just use that straight away.
                if ($squareUrlOverride) {
                    if ($this->logger) {
                        $this->logger->info('Found packshot_square_url_override');
                    }
                    $squareUrl = $squareUrlOverride;
                }
                // Fallback for missing square images
                // We want to do this AFTER the square URL override, to avoid regex failures
                if ($headerUrl && !$squareUrl) {
                    $squareUrl = $packshotBuilder->getSquareUrl($headerUrl);
                }
                // Download away!
                if ($squareUrl || $headerUrl) {
                    //DownloadImageFactory::downloadFromStoreUrl($game, $squareUrl, $headerUrl, $this->logger);
                    $downloadByOverrideUrl = new DownloadByOverrideUrl($game);
                    $downloadByOverrideUrl->download($squareUrl, $headerUrl);
                }
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
    }

    public function isEligibleForDownload(Game $game)
    {
        $isEligible = false;

        $hasValidDSItem = false;
        $hasBrokenDSItem = false;
        $hasStoreOverride = false;

        if ($this->hasValidDataSourceItem($game)) {
            $dsItem = $game->dspNintendoCoUk()->first();
            $hasValidDSItem = true;
        } elseif ($game->eshop_europe_fs_id) {
            // Broken link, but the images may still exist
            $hasBrokenDSItem = true;
        } elseif ($game->nintendo_store_url_override) {
            $hasStoreOverride = true;
        }

        $imgSquare = $game->image_square;
        $imgHeader = $game->image_header;

        if (!$hasValidDSItem && !$hasStoreOverride) {
            //$this->logger->info('No viable method to download images, so game is not eligible');
            $isEligible = false;
            return $isEligible;
        }

        if (!$imgSquare) {
            //$this->logger->info('Eligible for image download: image_square is blank');
            $isEligible = true;
            return $isEligible;
        }

        if (!$imgHeader) {
            //$this->logger->info('Eligible for image download: image_header is blank');
            $isEligible = true;
            return $isEligible;
        }

        $gameImages = new Images($game);

        $imgSquareFullPath = $gameImages->generateCurrentPathSquare($game);
        if (!file_exists($imgSquareFullPath)) {
            //$this->logger->info('Eligible for download: square image not found on file system ['.$imgSquareFullPath.']');
            $isEligible = true;
            return $isEligible;
        }

        $imgHeaderFullPath = $gameImages->generateCurrentPathHeader($game);
        if (!file_exists($imgHeaderFullPath)) {
            //$this->logger->info('Eligible for download: header image not found on file system ['.$imgHeaderFullPath.']');
            $isEligible = true;
            return $isEligible;
        }

        return $isEligible;
    }

    public function hasValidDataSourceItem(Game $game)
    {
        return $game->dspNintendoCoUk()->first() != null;
    }
}