<?php


namespace App\Domain\Game;

use App\Models\Game;
use App\Domain\Cache\CacheManager;
use Illuminate\Support\Facades\Cache;

class Repository
{
    public function __construct(
        private CacheManager $cache
    ){

    }
    public function markAsReleased(Game $game)
    {
        $dateNow = new \DateTime('now');

        $game->eu_is_released = 1;
        $game->eu_released_on = $dateNow->format('Y-m-d H:i:s');
        $game->save();
    }

    public function delete($gameId)
    {
        Game::where('id', $gameId)->delete();
    }

    public function find($id)
    {
        $game = $this->cache->remember("game-$id-core-data", 86400, function() use ($id) {
            return Game::find($id);
        });
        return $game;
    }

    public function searchByTitle($keywords)
    {
        return Game::where('title', 'like', '%'.$keywords.'%')->orderBy('eu_release_date', 'DESC')->get();
    }

    public function randomGame()
    {
        return Game::where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->where('is_low_quality', 0)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->inRandomOrder()
            ->first();
    }

    public function partialTitleSearch($title)
    {
        return Game::where('title', 'LIKE', '%'.$title.'%')->orderBy('title', 'asc')->get();
    }

    /**
     * @param $title
     * @param null $excludeGameId
     * @return bool
     */
    public function titleExists($title, $excludeGameId = null): bool
    {
        $game = Game::where('title', $title);
        if ($excludeGameId) {
            $game = $game->where('id', '<>', $excludeGameId);
        }
        $game = $game->first();
        return $game != null;
    }

    /**
     * @param $title
     * @return \App\Models\Game|null
     */
    public function getByTitle($title)
    {
        return Game::where('title', $title)->first();
    }

    /**
     * @param $idList
     * @param string[] $orderBy
     * @return \Illuminate\Support\Collection
     */
    public function getByIdList($idList, $orderBy = "")
    {
        if ($orderBy) {
            list($orderField, $orderDir) = $orderBy;
        } else {
            list($orderField, $orderDir) = ['id', 'desc'];
        }

        $idList = str_replace('&quot;', '', $idList);
        $idList = explode(",", $idList);

        $games = Game::whereIn('games.id', $idList)->orderBy($orderField, $orderDir);

        return $games->get();
    }

    public function getByEshopEuropeId($linkId)
    {
        return Game::where('eshop_europe_fs_id', $linkId)->first();
    }
}