<?php


namespace App\Services;

use App\Models\CampaignGame;
use Illuminate\Support\Facades\DB;

class CampaignGameService
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

    public function getByCampaign($campaignId)
    {
        return CampaignGame::where('campaign_id', $campaignId)->get();
    }

    public function getByCampaignNumeric($campaignId)
    {
        return CampaignGame::where('campaign_id', $campaignId)->orderBy('game_id', 'asc')->get();
    }

    public function countRankedGames($campaignId)
    {
        $gamesList = DB::select('
            SELECT count(*) AS count FROM campaign_games cg
            JOIN games g ON cg.game_id = g.id
            WHERE cg.campaign_id = ?
            AND g.review_count > 2
        ', [$campaignId]);

        return $gamesList[0]->count;
    }
}