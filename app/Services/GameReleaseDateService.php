<?php


namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Facades\DB;

class GameReleaseDateService
{

    /**
     * @return mixed
     */
    public function getAllWithNoEuReleaseDate()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->whereNull('games.eu_release_date')
            ->orderBy('games.id', 'desc')
            ->get();

        return $games;
    }

    // ********************************************** //

    /**
     * @param $seriesId
     * @param int $limit
     * @return mixed
     */
    public function getBySeries($seriesId, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.series_id', $seriesId)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param string $letter
     * @param int $limit
     * @return mixed
     */
    public function getReleasedByLetter($letter, $limit = null)
    {
        $games = Game::where('eu_is_released', 1)
            ->where('title', 'LIKE', $letter.'%')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $filter
     * @param $gameIdsReviewedBySite
     * @return mixed
     * @throws \Exception
     */
    public function getUnrankedByList($filter, $gameIdsReviewedBySite)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('review_count', '<', '3')
            ->whereNotIn('games.id', $gameIdsReviewedBySite);

        switch ($filter) {
            case 'aca-neogeo':
                $games = $games->where('games.title', 'LIKE', 'ACA NeoGeo %');
                break;
            case 'arcade-archives':
                $games = $games->where('games.title', 'LIKE', 'Arcade Archives %');
                break;
            case 'all-others':
                $games = $games->where('games.title', 'NOT LIKE', 'ACA NeoGeo %');
                $games = $games->where('games.title', 'NOT LIKE', 'Arcade Archives %');
                break;
            default:
                throw new \Exception('Unknown filter: '.$filter);
        }

        $games = $games->orderBy('games.eu_release_date', 'desc')
            ->orderBy('games.title', 'asc')
            ->get();

        return $games;
    }
}