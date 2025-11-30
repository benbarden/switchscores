<?php


namespace App\Domain\Game;

use App\Domain\Repository\AbstractRepository;
use App\Models\Game;
use App\Enums\CacheDuration;

class Repository extends AbstractRepository
{
    protected function getCachePrefix(): string
    {
        return "game";
    }

    public function clearCacheCoreData($gameId)
    {
        $cacheKey = $this->buildCacheKey("$gameId-core-data");
        $this->clearCache($cacheKey);
    }

    public function markAsReleased(Game $game)
    {
        $dateNow = new \DateTime('now');

        $game->eu_is_released = 1;
        $game->eu_released_on = $dateNow->format('Y-m-d H:i:s');
        $game->save();
    }

    public function unmarkAsReleased(Game $game)
    {
        $game->eu_is_released = 0;
        $game->eu_released_on = null;
        $game->save();
    }

    public function delete($gameId)
    {
        Game::where('id', $gameId)->delete();
    }

    public function find($id)
    {
        $cacheKey = $this->buildCacheKey("$id-core-data");
        return $this->rememberCache($cacheKey, CacheDuration::ONE_DAY, function() use ($id) {
            return Game::find($id);
        });
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

    public function partialTitleSearch($title, $includeLowQuality = true, $includeDeListed = true, $sortBy = null, $limit = null)
    {
        $games = Game::where('title', 'LIKE', '%'.$title.'%');
        if (!$includeLowQuality) {
            $games = $games->where('is_low_quality', 0);
        }
        if (!$includeDeListed) {
            $games = $games->where('format_digital', '<>', Game::FORMAT_DELISTED);
        }
        match ($sortBy) {
            'newest' => $games = $games->orderBy('eu_release_date', 'desc')->orderBy('title', 'asc'),
            default => $games = $games->orderBy('title', 'asc'),
        };
        if ($limit) {
            $games = $games->limit($limit);
        }
        return $games->get();
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

    public function getConditionalByCategoryAndOrTag($categoryId = null, $tagId = null)
    {
        $query = Game::query();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($tagId) {
            $query->whereHas('gameTags', function ($q) use ($tagId) {
                $q->where('tag_id', $tagId);
            });
        }

        $games = $query->orderBy('title')->get();

        return $games;
    }
}