<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\MemberView;

class CampaignsController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use MemberView;

    public function show($campaignId)
    {
        $campaign = $this->getServiceCampaign()->find($campaignId);
        if (!$campaign) abort(404);

        $tableSort = "[6, 'asc'], [5, 'asc'], [3, 'desc'], [4, 'desc']";
        $bindings = $this->getBindingsDashboardGenericSubpage('View campaign: '.$campaign->name, $tableSort);

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

        $bindings['ReviewedGameIdList'] = $this->getServiceQuickReview()->getAllByUserGameIdList($this->getAuthId());

        return view('user.campaigns.show', $bindings);
    }
}
