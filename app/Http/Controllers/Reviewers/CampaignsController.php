<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class CampaignsController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function show($campaignId)
    {
        $bindings = [];

        $campaign = $this->getServiceCampaign()->find($campaignId);
        if (!$campaign) abort(404);

        $pageTitle = 'View campaign: '.$campaign->name;

        // Campaigns
        $bindings['CampaignData'] = $campaign;

        $gameList = [];
        foreach ($campaign->games as $gameItem) {

            $gameId = $gameItem->game->id;
            $game = $this->getServiceGame()->find($gameId);
            if ($game) {
                $gameList[] = $game;
            }

        }

        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[3, 'asc'], [2, 'desc']";

        $authUser = $this->getValidUser($this->getServiceUser());
        $partnerId = $authUser->partner_id;
        $bindings['ReviewedGameIdList'] = $this->getServiceReviewLink()->getGameIdsReviewedBySite($partnerId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.campaigns.show', $bindings);
    }
}
