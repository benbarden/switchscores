<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\CampaignGame;

class CampaignGameService
{
    public function create($campaignId, $gameId)
    {
        CampaignGame::create([
            'campaign_id' => $campaignId,
            'game_id' => $gameId,
        ]);
    }

    public function edit(CampaignGame $campaignGame, $campaignId, $gameId)
    {
        $values = [
            'campaign_id' => $campaignId,
            'game_id' => $gameId,
        ];

        $campaignGame->fill($values);
        $campaignGame->save();
    }

    public function delete($id)
    {
        CampaignGame::where('id', $id)->delete();
    }

    public function deleteAllByCampaign($campaignId)
    {
        CampaignGame::where('campaign_id', $campaignId)->delete();
    }

    public function find($id)
    {
        return CampaignGame::find($id);
    }

    public function getByCampaign($campaignId)
    {
        return CampaignGame::where('campaign_id', $campaignId)->get();
    }

    public function getByCampaignNumeric($campaignId)
    {
        return CampaignGame::where('campaign_id', $campaignId)->orderBy('game_id', 'asc')->get();
    }

    public function campaignHasGame($campaignId, $gameId)
    {
        $campaignGame = CampaignGame::where('campaign_id', $campaignId)->where('game_id', $gameId)->first();
        if ($campaignGame) {
            return true;
        } else {
            return false;
        }
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