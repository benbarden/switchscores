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

    public function findWithFilters(array $filters, int $userId = null)
    {
        $query = Game::query()
            ->with('category')
            ->where('eu_is_released', 1)
            ->active();

        // Keywords
        if (!empty($filters['keywords'])) {
            $query->where('title', 'like', '%' . $filters['keywords'] . '%');
        }

        // Category (including children)
        if (!empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];
            $query->where(function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhereHas('category', function ($q2) use ($categoryId) {
                      $q2->where('parent_id', $categoryId);
                  });
            });
        }

        // Console (Switch 1 / Switch 2)
        if (!empty($filters['console_id'])) {
            $query->where('console_id', (int) $filters['console_id']);
        }

        // Ranked games only (has game_rank)
        if (!empty($filters['ranked_only'])) {
            $query->whereNotNull('game_rank');
        }

        // Minimum rating
        if (!empty($filters['min_rating'])) {
            $query->where('rating_avg', '>=', (float) $filters['min_rating']);
        }

        // Multiplayer options
        if (!empty($filters['has_local_multiplayer'])) {
            $query->where('has_local_multiplayer', 1);
        }
        if (!empty($filters['has_online_play'])) {
            $query->where('has_online_play', 1);
        }

        // Minimum players
        if (!empty($filters['min_players'])) {
            $query->where('players', '>=', (int) $filters['min_players']);
        }

        // Play modes
        if (!empty($filters['play_mode_tv'])) {
            $query->where('play_mode_tv', 1);
        }
        if (!empty($filters['play_mode_tabletop'])) {
            $query->where('play_mode_tabletop', 1);
        }
        if (!empty($filters['play_mode_handheld'])) {
            $query->where('play_mode_handheld', 1);
        }

        // Exclude owned games
        if (!empty($filters['exclude_owned']) && $userId) {
            $query->whereDoesntHave('userCollection', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        // Exclude ignored games
        if (!empty($filters['exclude_ignored']) && !empty($filters['ignored_game_ids'])) {
            $query->whereNotIn('id', $filters['ignored_game_ids']);
        }

        return $query->orderBy('rating_avg', 'DESC')
            ->orderBy('title', 'ASC')
            ->limit(100)
            ->get();
    }

    public function randomGame()
    {
        return Game::where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->where('is_low_quality', 0)
            ->active()
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
            $games = $games->active();
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
     * Get all games matching a title (may exist on multiple consoles)
     */
    public function getAllByTitle($title): \Illuminate\Support\Collection
    {
        return Game::where('title', $title)->get();
    }

    /**
     * Check if a title exists for a specific console
     */
    public function titleExistsForConsole($title, $consoleId, $excludeGameId = null): bool
    {
        $game = Game::where('title', $title)->where('console_id', $consoleId);
        if ($excludeGameId) {
            $game = $game->where('id', '<>', $excludeGameId);
        }
        return $game->exists();
    }

    /**
     * Get a game by title and console
     */
    public function getByTitleAndConsole($title, $consoleId)
    {
        return Game::where('title', $title)->where('console_id', $consoleId)->first();
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

    public function getBatchDates($limit = 12)
    {
        return Game::query()
            ->selectRaw('DISTINCT added_batch_date')
            ->whereNotNull('added_batch_date')
            ->orderByDesc('added_batch_date')
            ->limit(12)
            ->pluck('added_batch_date');
    }

    /**
     * Find a game with the same link_title on a different console
     */
    public function getByLinkTitleOnOtherConsole($linkTitle, $currentConsoleId)
    {
        return Game::where('link_title', $linkTitle)
            ->where('console_id', '!=', $currentConsoleId)
            ->active()
            ->first();
    }
}