<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Campaign\Repository as CampaignRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;

class CampaignsController extends Controller
{
    public function __construct(
        private CampaignRepository $repoCampaign,
        private GameRepository $repoGame,
        private ReviewLinkRepository $repoReviewLink,
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
                    $game = $this->repoGame->find($gameId);
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
        $bindings['ReviewedGameIdList'] = $this->repoReviewLink->bySiteGameIdList($partnerId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.campaigns.show', $bindings);
    }
}
