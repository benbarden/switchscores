<?php


namespace App\Domain\Tag;

use Illuminate\Support\Facades\DB;

use App\Domain\Game\PackshotJoin;
use App\Enums\GameStatus;
use App\Models\Tag;
use App\Models\Game;
use App\Models\TagCategory;

class Repository
{
    public function create($tagName, $linkTitle, $tagCategoryId, $taxonomyReviewed = 0,
        $layoutVersion = null, $metaDescription = "", $introDescription = "")
    {
        Tag::create([
            'tag_name' => $tagName,
            'link_title' => $linkTitle,
            'tag_category_id' => $tagCategoryId,
            'taxonomy_reviewed' => $taxonomyReviewed,
            'layout_version' => $layoutVersion,
            'meta_description' => $metaDescription,
            'intro_description' => $introDescription,
        ]);
    }

    public function edit(Tag $tagData, $tagName, $linkTitle, $tagCategoryId, $taxonomyReviewed = 0,
        $layoutVersion = null, $metaDescription = "", $introDescription = "")
    {
        $values = [
            'tag_name' => $tagName,
            'link_title' => $linkTitle,
            'tag_category_id' => $tagCategoryId,
            'taxonomy_reviewed' => $taxonomyReviewed,
            'layout_version' => $layoutVersion,
            'meta_description' => $metaDescription,
            'intro_description' => $introDescription,
        ];

        $tagData->fill($values);
        $tagData->save();
    }

    public function delete($tagId)
    {
        Tag::where('id', $tagId)->delete();
    }

    public function find($tagId)
    {
        return Tag::find($tagId);
    }

    public function getByLinkTitle($linkTitle)
    {
        return Tag::where('link_title', $linkTitle)->first();
    }

    public function getAll()
    {
        return Tag::orderBy('tag_name', 'asc')->get();
    }

    public function getByTagCategory($tagCategoryId)
    {
        return Tag::where('tag_category_id', $tagCategoryId)->orderBy('tag_name')->get();
    }

    public function getAllCategorised()
    {
        return TagCategory::with(['tags' => function ($q) {
                $q->orderBy('tag_name');
        }])
        ->orderBy('category_order')
        ->get();
    }

    public function gamesByTag($tagId)
    {
        return Game::whereHas('gameTags', function($query) use ($tagId) {
            $query->where('tag_id', $tagId);
        })->get();
    }

    public function gamesByCategoryAndTag($categoryId, $tagId)
    {
        return Game::where('category_id', $categoryId)
            ->whereHas('gameTags', function($query) use ($tagId) {
                $query->where('tag_id', $tagId);
            })->get();
    }

    public function rankedByTag($consoleId, $tagId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'games.id AS game_id',
                'game_tags.id AS game_tag_id',
                'tags.tag_name')
            ->where('games.console_id', $consoleId)
            ->where('game_tags.tag_id', $tagId)
            ->whereNotNull('games.game_rank')
            ->where('games.game_status', GameStatus::ACTIVE->value)
            ->where('games.is_low_quality', 0)
            ->orderBy('games.game_rank', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function unrankedByTag($consoleId, $tagId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'games.id AS game_id',
                'game_tags.id AS game_tag_id',
                'tags.tag_name')
            ->where('games.console_id', $consoleId)
            ->where('game_tags.tag_id', $tagId)
            ->whereNull('games.game_rank')
            ->where('games.game_status', GameStatus::ACTIVE->value)
            ->where('games.is_low_quality', 0)
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function delistedByTag($consoleId, $tagId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'games.id AS game_id',
                'game_tags.id AS game_tag_id',
                'tags.tag_name')
            ->where('games.console_id', $consoleId)
            ->where('game_tags.tag_id', $tagId)
            ->whereNull('games.game_rank')
            ->where('games.game_status', GameStatus::DELISTED->value)
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }
    public function rankedByTagMerged($tagId, $consoleId = null, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->select('games.*', 'games.id AS game_id')
            ->where('game_tags.tag_id', $tagId)
            ->whereNotNull('games.game_rank')
            ->where('games.game_status', GameStatus::ACTIVE->value)
            ->where('games.is_low_quality', 0)
            ->orderBy('games.game_rank', 'asc')
            ->orderBy('games.title', 'asc');

        PackshotJoin::apply($games);

        if ($consoleId) {
            $games->where('games.console_id', $consoleId);
        }

        if ($limit) {
            $games->limit($limit);
        }

        return $games->get();
    }

    public function hiddenGemsByTagMerged($tagId, $consoleId = null, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->select('games.*', 'games.id AS game_id')
            ->where('game_tags.tag_id', $tagId)
            ->whereIn('games.review_count', [1, 2])
            ->where('games.game_status', GameStatus::ACTIVE->value)
            ->where('games.is_low_quality', 0)
            ->orderBy('games.rating_avg', 'desc');

        PackshotJoin::apply($games);

        if ($consoleId) {
            $games->where('games.console_id', $consoleId);
        }

        if ($limit) {
            $games->limit($limit);
        }

        return $games->get();
    }

    public function getSnapshotStatsByTagMerged($tagId, $consoleId = null): array
    {
        $base = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->where('game_tags.tag_id', $tagId);

        if ($consoleId) {
            $base->where('games.console_id', $consoleId);
        }

        $total = (clone $base)->where('games.game_status', GameStatus::ACTIVE->value)->where('games.is_low_quality', 0)->where('games.eu_is_released', 1)->count();
        $ranked = (clone $base)->where('games.review_count', '>=', 3)->where('games.game_status', GameStatus::ACTIVE->value)->where('games.is_low_quality', 0)->where('games.eu_is_released', 1)->count();
        $reviewedButUnranked = (clone $base)->whereIn('games.review_count', [1, 2])->where('games.game_status', GameStatus::ACTIVE->value)->where('games.is_low_quality', 0)->where('games.eu_is_released', 1)->count();
        $noReviews = (clone $base)->where('games.review_count', 0)->where('games.game_status', GameStatus::ACTIVE->value)->where('games.is_low_quality', 0)->where('games.eu_is_released', 1)->count();

        return [
            'total'               => $total,
            'ranked'              => $ranked,
            'reviewedButUnranked' => $reviewedButUnranked,
            'noReviews'           => $noReviews,
        ];
    }

    public function lowQualityByTag($consoleId, $tagId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'games.id AS game_id',
                'game_tags.id AS game_tag_id',
                'tags.tag_name')
            ->where('games.console_id', $consoleId)
            ->where('game_tags.tag_id', $tagId)
            ->where('games.game_status', GameStatus::ACTIVE->value)
            ->where('games.is_low_quality', 1)
            ->orderBy('games.game_rank', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function listByTagMerged($tagId, $page, $perPage, $filter, $sort, $consoleId = null)
    {
        $query = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->select('games.*', 'games.id AS game_id')
            ->where('game_tags.tag_id', $tagId)
            ->where('games.game_status', GameStatus::ACTIVE->value)
            ->where('games.is_low_quality', 0)
            ->where('games.eu_is_released', 1);

        if ($consoleId) {
            $query->where('games.console_id', $consoleId);
        }

        switch ($filter) {
            case 'ranked':
                $query->where('games.review_count', '>=', 3);
                break;
            case 'hidden':
                $query->whereIn('games.review_count', [1, 2]);
                break;
            case 'noreviews':
                $query->where('games.review_count', 0);
                break;
        }

        switch ($sort) {
            case 'title_asc':
                $query->orderBy('games.title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('games.title', 'desc');
                break;
            case 'rating_desc':
                $query->orderBy('games.rating_avg', 'desc');
                break;
            case 'rating_asc':
                $query->orderBy('games.rating_avg', 'asc');
                break;
            case 'release_desc':
                $query->orderBy('games.eu_release_date', 'desc');
                break;
            case 'release_asc':
                $query->orderBy('games.eu_release_date', 'asc');
                break;
        }

        $total = $query->count();
        $pages = max((int) ceil($total / $perPage), 1);

        if ($page > $pages) {
            $page = $pages;
        }

        $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return ['items' => $items, 'page' => $page, 'pages' => $pages, 'total' => $total];
    }
}