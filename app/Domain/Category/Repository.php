<?php


namespace App\Domain\Category;

use App\Models\Category;
use App\Models\Game;

class Repository
{
    public function create($name, $linkTitle, $blurbOption, $parentId = null)
    {
        Category::create([
            'name' => $name,
            'link_title' => $linkTitle,
            'blurb_option' => $blurbOption,
            'parent_id' => $parentId,
        ]);
    }

    public function edit(Category $category, $name, $linkTitle, $blurbOption, $parentId = null)
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
            'blurb_option' => $blurbOption,
            'parent_id' => $parentId,
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
}