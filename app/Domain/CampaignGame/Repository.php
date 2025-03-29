<?php

namespace App\Domain\CampaignGame;

use App\Models\CampaignGame;

class Repository
{
    public function create($campaignId, $gameId)
    {
        CampaignGame::create([
            'campaign_id' => $campaignId,
            'game_id' => $gameId,
        ]);
    }

    public function deleteAllByCampaign($campaignId)
    {
        CampaignGame::where('campaign_id', $campaignId)->delete();
    }

    public function byCampaign($campaignId)
    {
        return CampaignGame::where('campaign_id', $campaignId)->get();
    }

    public function byCampaignNumeric($campaignId)
    {
        return CampaignGame::where('campaign_id', $campaignId)->orderBy('game_id', 'asc')->get();
    }
}