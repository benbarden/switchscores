<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

use App\Domain\Campaign\Repository as CampaignRepository;

class CampaignsController extends Controller
{
    use SwitchServices;

    public function __construct(
        private CampaignRepository $repoCampaign
    )
    {
    }

    public function show($campaignId)
    {
        $bindings = [];

        $campaign = $this->repoCampaign->find($campaignId);
        if (!$campaign) abort(404);

        $pageTitle = 'View campaign: '.$campaign->name;

        // Campaigns
        $bindings['CampaignData'] = $campaign;

        $gameList = [];
        foreach ($campaign->games as $gameItem) {

            if ($gameItem->game) {
                $gameId = $gameItem->game->id;
                if ($gameId) {
                    $game = $this->getServiceGame()->find($gameId);
                    if ($game) {
                        $gameList[] = $game;
                    }
                }
            }

        }

        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[3, 'asc'], [2, 'desc']";

        $currentUser = resolve('User/Repository')->currentUser();
        $partnerId = $currentUser->partner_id;
        $bindings['ReviewedGameIdList'] = $this->getServiceReviewLink()->getGameIdsReviewedBySite($partnerId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.campaigns.show', $bindings);
    }
}
