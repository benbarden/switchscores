<?php

namespace App\Http\Controllers\Members;

use App\Domain\Campaign\Repository as CampaignRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\QuickReview\Repository as QuickReviewRepository;
use Illuminate\Routing\Controller as Controller;

class CampaignsController extends Controller
{
    public function __construct(
        private CampaignRepository $repoCampaign,
        private GameRepository $repoGame,
        private QuickReviewRepository $repoQuickReview
    )
    {
    }

    public function show($campaignId)
    {
        $campaign = $this->repoCampaign->find($campaignId);
        if (!$campaign) abort(404);

        $tableSort = "[6, 'asc'], [5, 'asc'], [3, 'desc'], [4, 'desc']";

        $pageTitle = 'View campaign: '.$campaign->name;
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Member')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $bindings['CampaignData'] = $campaign;

        $gameList = [];

        foreach ($campaign->games as $gameItem) {

            if ($gameItem->game) {
                $gameId = $gameItem->game->id;
                $game = $this->repoGame->find($gameId);
                if ($game) {
                    $gameList[] = $game;
                }
            }

        }

        $bindings['GameList'] = $gameList;

        $currentUser = resolve('User/Repository')->currentUser();

        $bindings['ReviewedGameIdList'] = $this->repoQuickReview->byUserGameIdList($currentUser->id);

        return view('members.campaigns.show', $bindings);
    }
}
