<?php


namespace App\Domain\Category;

use App\Models\Category;
use App\Models\Game;

class Repository
{
    public function create($name, $linkTitle, $blurbOption, $parentId = null, $taxonomyReviewed = 0,
       $layoutVersion = null, $metaDescription = "", $introDescription = "")
    {
        Category::create([
            'name' => $name,
            'link_title' => $linkTitle,
            'blurb_option' => $blurbOption,
            'parent_id' => $parentId,
            'taxonomy_reviewed' => $taxonomyReviewed,
            'layout_version' => $layoutVersion,
            'meta_description' => $metaDescription,
            'intro_description' => $introDescription,
        ]);
    }

    public function edit(Category $category, $name, $linkTitle, $blurbOption, $parentId = null, $taxonomyReviewed = 0,
        $layoutVersion = null, $metaDescription = "", $introDescription = "")
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
            'blurb_option' => $blurbOption,
            'parent_id' => $parentId,
            'taxonomy_reviewed' => $taxonomyReviewed,
            'layout_version' => $layoutVersion,
            'meta_description' => $metaDescription,
            'intro_description' => $introDescription,
        ];

        $category->fill($values);
        $category->save();
    }

    public function delete($id)
    {
        Category::where('id', $id)->delete();
    }

    public function find($id)
    {
        return Category::find($id);
    }

    public function getByLinkTitle($linkTitle)
    {
        return Category::where('link_title', $linkTitle)->first();
    }

    public function getByName($name)
    {
        return Category::where('name', $name)->first();
    }

    public function getAll()
    {
        return Category::orderBy('name', 'asc')->get();
    }

    public function topLevelCategories()
    {
        return Category::whereDoesntHave('parent')->orderBy('name', 'asc')->get();
    }

    public function categoryChildren($categoryId)
    {
        return Category::where('parent_id', $categoryId)->orderBy('name', 'asc')->get();
    }

    public function gamesByCategory($categoryId)
    {
        return Game::where('category_id', $categoryId)->orderBy('title', 'asc')->get();
    }

    public function rankedByCategory($consoleId, $categoryId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->orderBy('game_rank', 'asc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function hiddenGemsByCategory($consoleId, $categoryId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->whereIn('review_count', [1, 2])
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->orderBy('rating_avg', 'desc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function unrankedByCategory($consoleId, $categoryId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->whereNull('game_rank')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->orderBy('review_count', 'desc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function delistedByCategory($consoleId, $categoryId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->whereNull('game_rank')
            ->where('format_digital', '=', Game::FORMAT_DELISTED)
            ->orderBy('title', 'asc')
            ->orderBy('eu_release_date', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function lowQualityByCategory($consoleId, $categoryId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('category_id', $categoryId)
            ->where('eu_is_released', 1)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 1)
            ->orderBy('title', 'asc')
            ->orderBy('eu_release_date', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function getSnapshotStats(Category $category, $consoleId): array
    {
        // Base query for games in this category
        $games = Game::where('category_id', $category->id)
            ->where('console_id', $consoleId);

        $total = (clone $games)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->where('eu_is_released', 1)
            ->count();

        $ranked = (clone $games)
            ->where('review_count', '>=', 3)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->where('eu_is_released', 1)
            ->count();

        $reviewedButUnranked = (clone $games)
            ->whereIn('review_count', [1, 2])
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->where('eu_is_released', 1)
            ->count();

        $noReviews = (clone $games)
            ->whereIn('review_count', [0])
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->where('eu_is_released', 1)
            ->count();

        $lowQuality = (clone $games)
            ->where('is_low_quality', 1)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->count();

        $delisted = (clone $games)
            ->where('format_digital', Game::FORMAT_DELISTED)
            ->count();

        // Newest release
        $newest = (clone $games)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->where('eu_is_released', 1)
            ->orderBy('eu_release_date', 'desc')
            ->value('eu_release_date');

        return [
            'total'      => $total,
            'ranked'     => $ranked,
            'reviewedButUnranked'   => $reviewedButUnranked,
            'noReviews'  => $noReviews,
            'lowQuality' => $lowQuality,
            'delisted'   => $delisted,
            'newest'     => $newest,
        ];
    }

    public function listByCategory($consoleId, $categoryId, $page, $perPage, $filter, $sort)
    {
        $query = Game::where('category_id', $categoryId)
            ->where('console_id', $consoleId)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->where('eu_is_released', 1);

        switch ($filter) {
            case 'ranked':
                $query->where('review_count', '>=', 3);
                break;

            case 'hidden':
                $query->whereIn('review_count', [1, 2]);
                break;

            case 'noreviews':
                $query->where('review_count', 0);
                break;
        }

        switch ($sort) {
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;

            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;

            case 'rating_desc':
                $query->orderBy('rating_avg', 'desc');
                break;

            case 'rating_asc':
                $query->orderBy('rating_avg', 'asc');
                break;

            case 'release_desc':
                $query->orderBy('eu_release_date', 'desc');
                break;

            case 'release_asc':
                $query->orderBy('eu_release_date', 'asc');
                break;
        }

        $total = $query->count();
        $pages = max((int) ceil($total / $perPage), 1);

        if ($page > $pages) {
            $page = $pages;
        }

        $items = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return ['items' => $items, 'page' => $page, 'pages' => $pages, 'total' => $total];
    }
}