<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Factories\GameDirectorFactory;

use App\Construction\GameReleaseDate\Director as GameReleaseDateDirector;
use App\Construction\GameReleaseDate\Builder as GameReleaseDateBuilder;

use App\Services\UrlService;

use App\Events\GameCreated;

use App\Factories\EshopEuropeUpdateGameFactory;
use App\Factories\EshopEuropeRedownloadPackshotsFactory;

use App\Traits\SwitchServices;

class FeedItemEshopEuropeController extends Controller
{
    use SwitchServices;

    public function showList($report = null)
    {
        $bindings = [];

        $serviceEshopGame = $this->getServiceEshopEuropeGame();

        $jsInitialSort = "[ 1, 'asc']";
        if ($report == null) {
            $bindings['ActiveNav'] = 'all';
            $feedItems = $serviceEshopGame->getAll();
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'with-link':
                    $feedItems = $serviceEshopGame->getAllWithLink();
                    break;
                case 'no-link':
                    $feedItems = $serviceEshopGame->getAllWithoutLink();
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        $bindings['TopTitle'] = 'Admin - Feed items - eShop: Europe';
        $bindings['PageTitle'] = 'Feed items - eShop: Europe';
        $bindings['FeedItems'] = $feedItems;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.feed-items.eshop.europe.list', $bindings);
    }

    public function view($itemId)
    {
        $bindings = [];

        $serviceEshopGame = $this->getServiceEshopEuropeGame();
        $gameData = $serviceEshopGame->getByFsId($itemId);
        if (!$gameData) abort(404);

        $bindings['GameData'] = $gameData->toArray();

        $bindings['TopTitle'] = 'Admin - Feed items - eShop: Europe - '.$gameData->title;
        $bindings['PageTitle'] = $gameData->title.' - Feed items - eShop: Europe';

        return view('admin.feed-items.eshop.europe.view', $bindings);
    }

    public function addGame($itemId)
    {
        $bindings = [];
        $customErrors = [];

        $serviceEshopGame = $this->getServiceEshopEuropeGame();
        $serviceGame = $this->getServiceGame();
        $serviceGameTitleHash = $this->getServiceGameTitleHash();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();
        $serviceUrl = new UrlService();

        $eshopGameData = $serviceEshopGame->getByFsId($itemId);
        if (!$eshopGameData) abort(404);

        $bindings['EshopGameData'] = $eshopGameData->toArray();

        $bindings['TopTitle'] = 'Add game from eShop Europe feed';
        $bindings['PageTitle'] = 'Add game from eShop Europe feed';

        $bindings['FsId'] = $itemId;

        $request = request();
        $okToProceed = true;

        if ($request->isMethod('post')) {

            $title = $eshopGameData->title;

            // Check title hash is unique
            $titleHash = $serviceGameTitleHash->generateHash($title);
            $existingTitleHash = $serviceGameTitleHash->getByHash($titleHash);

            // Check for duplicates
            if ($existingTitleHash != null) {
                $customErrors[] = 'Title already exists for another record! Game id: '.$existingTitleHash->game_id;
                $okToProceed = false;
            }

            if ($okToProceed) {

                // Generate usable game data
                $linkText = $serviceUrl->generateLinkText($title);

                $gameData = [
                    'title' => $title,
                    'link_title' => $linkText,
                    'eshop_europe_fs_id' => $eshopGameData->fs_id,
                ];

                // Save details
                $game = GameDirectorFactory::createNew($gameData);
                $gameId = $game->id;

                // Add title hash
                $gameTitleHash = $serviceGameTitleHash->create($title, $titleHash, $gameId);

                // Update release dates
                $gameReleaseDateDirector = new GameReleaseDateDirector();

                $regionsToUpdate = $gameReleaseDateDirector->getRegionList();

                foreach ($regionsToUpdate as $region) {

                    $gameReleaseDateBuilder = new GameReleaseDateBuilder();
                    $gameReleaseDateDirector->setBuilder($gameReleaseDateBuilder);
                    $gameReleaseDateDirector->buildNewReleaseDate($region, $gameId, []);
                    $gameReleaseDate = $gameReleaseDateBuilder->getGameReleaseDate();
                    $gameReleaseDate->save();

                }

                // Update eShop data
                EshopEuropeUpdateGameFactory::updateGame($game);
                EshopEuropeRedownloadPackshotsFactory::redownloadPackshots($game);

                // Now fix the release year
                foreach ($regionsToUpdate as $region) {

                    $gameReleaseDateBuilder = new GameReleaseDateBuilder();
                    $gameReleaseDateDirector->setBuilder($gameReleaseDateBuilder);

                    $gameReleaseDateExisting = $serviceGameReleaseDate->getByGameAndRegion($gameId, $region);
                    if ($gameReleaseDateExisting) {
                        $releaseDate = $gameReleaseDateExisting->release_date;
                        $releaseYear = $gameReleaseDateExisting->release_year;
                        $isReleased = $gameReleaseDateExisting->is_released;
                        if (!$releaseYear) {
                            $releaseYear = $serviceGameReleaseDate->getReleaseYear($releaseDate);
                            $gameReleaseDateExisting->release_year = $releaseYear;
                            $gameReleaseDateExisting->save();
                        }
                        if ($region == 'eu') {
                            if ($isReleased == 1) {
                                $dateNow = new \DateTime('now');
                                $game->eu_released_on = $dateNow->format('Y-m-d H:i:s');
                                $game->save();
                            }
                        }
                    }

                }

                // Trigger event
                event(new GameCreated($game));

                return redirect('/staff/games/detail/'.$gameId.'?lastaction=add&lastgameid='.$gameId);

            }

        }

        $bindings['ErrorsCustom'] = $customErrors;

        return view('admin.feed-items.eshop.europe.add-game', $bindings);
    }
}
