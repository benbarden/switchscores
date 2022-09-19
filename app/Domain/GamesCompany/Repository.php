<?php


namespace App\Domain\GamesCompany;

use App\Models\GamesCompany;

class Repository
{
    public function editGamesCompany(
        GamesCompany $gamesCompany, $name, $linkTitle, $websiteUrl, $twitterId, $isLowQuality
    )
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
            'website_url' => $websiteUrl,
            'twitter_id' => $twitterId,
            'is_low_quality' => $isLowQuality,
        ];

        $gamesCompany->fill($values);
        $gamesCompany->save();
    }

    public function deleteGamesCompany($id)
    {
        GamesCompany::where('id', $id)->delete();
    }

    public function find($id)
    {
        return GamesCompany::find($id);
    }

    public function getByLinkTitle($linkTitle)
    {
        return GamesCompany::where('link_title', $linkTitle)->first();
    }

    public function getByName($name)
    {
        return GamesCompany::where('name', $name)->first();
    }

    public function getAll()
    {
        return GamesCompany::orderBy('name', 'asc')->get();
    }

    public function newestNormalQuality($limit = 10)
    {
        return GamesCompany::where('is_low_quality', 0)->orderBy('id', 'desc')->limit($limit)->get();
    }

    public function mostPublishedGames($limit = 10)
    {
        return GamesCompany::where('is_low_quality', 0)->withCount('publisherGames')->orderBy('publisher_games_count', 'desc')->limit($limit)->get();
    }

    public function normalQuality()
    {
        return GamesCompany::where('is_low_quality', 0)->orderBy('name', 'asc')->get();
    }

    public function lowQuality()
    {
        return GamesCompany::where('is_low_quality', 1)->orderBy('name', 'asc')->get();
    }

    public function normalQualityCount()
    {
        return GamesCompany::where('is_low_quality', 0)->orderBy('name', 'asc')->count();
    }

    public function lowQualityCount()
    {
        return GamesCompany::where('is_low_quality', 1)->orderBy('name', 'asc')->count();
    }

    public function searchGamesCompany($name)
    {
        return GamesCompany::where('name', 'LIKE', '%'.$name.'%')->orderBy('name', 'asc')->get();
    }

    public function getMergedGameList($gameDevList, $gamePubList)
    {
        $mergedGameList = [];
        $usedGameIds = [];

        if ($gameDevList && $gamePubList) {

            foreach ($gameDevList as $item) {
                $gameId = $item->id;
                $item->PartnerType = 'developer';
                $item->ExtraDetailLine = 'Developer';
                $mergedGameList[$gameId] = $item;
                $usedGameIds[] = $gameId;
            }
            foreach ($gamePubList as $item) {
                $gameId = $item->id;
                if (in_array($gameId, $usedGameIds)) {
                    $mergedGameList[$gameId]->PartnerType = 'dev/pub';
                    $mergedGameList[$gameId]->ExtraDetailLine = 'Dev/Pub';
                } else {
                    $item->PartnerType = 'publisher';
                    $item->ExtraDetailLine = 'Publisher';
                    $mergedGameList[] = $item;
                }
            }

        } elseif ($gameDevList) {

            $mergedGameList = $gameDevList;
            foreach ($gameDevList as $item) {
                $item->PartnerType = 'developer';
                $item->ExtraDetailLine = 'Developer';
                $mergedGameList[] = $item;
            }

        } elseif ($gamePubList) {

            $mergedGameList = $gamePubList;
            foreach ($gamePubList as $item) {
                $item->PartnerType = 'publisher';
                $item->ExtraDetailLine = 'Publisher';
                $mergedGameList[] = $item;
            }

        }

        return $mergedGameList;
    }

    // Outreach targets
    public function getPublishersWithUnrankedGames()
    {
        return GamesCompany::select(
            'games_companies.id', 'games_companies.name', 'games_companies.link_title',
            'games.id AS game_id', 'games.title AS game_title',
            'games.link_title AS game_link_title', 'games.review_count', 'games.rating_avg')
            ->join('game_publishers', 'game_publishers.publisher_id', '=', 'games_companies.id')
            ->join('games', 'games.id', '=', 'game_publishers.game_id')
            ->where('games.review_count', '<', 3)
            ->whereNull('games_companies.last_outreach_id')
            ->orderBy('games_companies.id', 'asc')
            ->orderBy('games.id', 'asc')
            ->get();
    }

    // Outreach targets
    public function getDevelopersWithUnrankedGames()
    {
        return GamesCompany::select(
            'games_companies.id', 'games_companies.name', 'games_companies.link_title',
            'games.id AS game_id', 'games.title AS game_title',
            'games.link_title AS game_link_title', 'games.review_count', 'games.rating_avg')
            ->join('game_developers', 'game_developers.developer_id', '=', 'games_companies.id')
            ->join('games', 'games.id', '=', 'game_developers.game_id')
            ->where('games.review_count', '<', 3)
            ->whereNull('games_companies.last_outreach_id')
            ->orderBy('games_companies.id', 'asc')
            ->orderBy('games.id', 'asc')
            ->get();
    }
}