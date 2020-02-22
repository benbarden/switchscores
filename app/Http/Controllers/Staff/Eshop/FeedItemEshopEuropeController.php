<?php

namespace App\Http\Controllers\Staff\Eshop;

use Illuminate\Routing\Controller as Controller;

use App\Services\UrlService;

use App\Events\GameCreated;

use App\Factories\GameDirectorFactory;
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
        $serviceEshopIgnore = $this->getServiceEshopEuropeIgnore();

        $ignoredIdList = $serviceEshopIgnore->getIgnoredFsIdList();

        $jsInitialSort = "[ 1, 'asc']";
        if ($report == null) {
            $bindings['ActiveNav'] = 'all';
            $feedItems = $serviceEshopGame->getAll();
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'with-link':
                    $feedItems = $serviceEshopGame->getAllWithLink($ignoredIdList);
                    break;
                case 'no-link':
                    $feedItems = $serviceEshopGame->getAllWithoutLink($ignoredIdList);
                    break;
                case 'ignored':
                    $feedItems = $serviceEshopGame->getByFsIdList($ignoredIdList);
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        $bindings['TopTitle'] = 'Staff - Feed items - eShop: Europe';
        $bindings['PageTitle'] = 'Feed items - eShop: Europe';
        $bindings['FeedItems'] = $feedItems;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('staff.eshop.feed-items-europe.list', $bindings);
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

        return view('staff.eshop.feed-items-europe.view', $bindings);
    }

    public function addGame($itemId)
    {
        $bindings = [];
        $customErrors = [];

        $serviceEshopGame = $this->getServiceEshopEuropeGame();
        $serviceGameTitleHash = $this->getServiceGameTitleHash();
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
            $titleLowercase = strtolower($title);
            $hashedTitle = $serviceGameTitleHash->generateHash($title);
            $existingTitleHash = $serviceGameTitleHash->getByHash($hashedTitle);

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
                $gameTitleHash = $serviceGameTitleHash->create($titleLowercase, $hashedTitle, $gameId);

                // Update eShop data
                EshopEuropeUpdateGameFactory::updateGame($game);
                EshopEuropeRedownloadPackshotsFactory::redownloadPackshots($game);

                // Trigger event
                event(new GameCreated($game));

                return redirect('/staff/games/detail/'.$gameId.'?lastaction=add&lastgameid='.$gameId);

            }

        }

        $bindings['ErrorsCustom'] = $customErrors;

        return view('staff.eshop.feed-items-europe.add-game', $bindings);
    }

    public function addToIgnoreList()
    {
        $serviceEshopEuropeGame = $this->getServiceEshopEuropeGame();
        $serviceEshopEuropeIgnore = $this->getServiceEshopEuropeIgnore();

        $request = request();

        $fsId = $request->fsId;
        if (!$fsId) {
            return response()->json(['error' => 'Missing data: fsId'], 400);
        }

        $eshopEuropeGame = $serviceEshopEuropeGame->getByFsId($fsId);
        if (!$eshopEuropeGame) {
            return response()->json(['error' => 'eShop Europe record not found for fsId: '.$fsId], 400);
        }

        $eshopEuropeIgnore = $serviceEshopEuropeIgnore->getByFsId($fsId);
        if ($eshopEuropeIgnore) {
            return response()->json(['error' => 'eShop Europe record is already marked as ignored'], 400);
        }

        $serviceEshopEuropeIgnore->add($fsId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeFromIgnoreList()
    {
        $serviceEshopEuropeGame = $this->getServiceEshopEuropeGame();
        $serviceEshopEuropeIgnore = $this->getServiceEshopEuropeIgnore();

        $request = request();

        $fsId = $request->fsId;
        if (!$fsId) {
            return response()->json(['error' => 'Missing data: fsId'], 400);
        }

        $eshopEuropeGame = $serviceEshopEuropeGame->getByFsId($fsId);
        if (!$eshopEuropeGame) {
            return response()->json(['error' => 'eShop Europe record not found for fsId: '.$fsId], 400);
        }

        $eshopEuropeIgnore = $serviceEshopEuropeIgnore->getByFsId($fsId);
        if (!$eshopEuropeIgnore) {
            return response()->json(['error' => 'eShop Europe record is not marked as ignored'], 400);
        }

        $serviceEshopEuropeIgnore->deleteByFsId($fsId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

}
