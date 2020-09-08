<?php


namespace App\Services;

use App\Category;
use Illuminate\Support\Facades\DB;

class CategoryService
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


    public function getBlurbOptions()
    {
        $options = [
            0 => 'None',
            1 => 'a (category) game',
            2 => 'an (category) game',
            3 => 'a (category)',
            4 => 'an (category)',
            5 => 'involves (category)',
        ];

        return $options;
    }

    public function parseBlurbOption(Category $category)
    {
        // Only convert if it's not an acronym
        if ($category->name == strtoupper($category->name)) {
            $categoryName = $category->name;
        } else {
            $categoryName = strtolower($category->name);
        }

        switch ($category->blurb_option) {
            case 0:
                $blurbText = '';
                break;
            case 1:
                $blurbText = sprintf('a %s game for the Nintendo Switch', $categoryName);
                break;
            case 2:
                $blurbText = sprintf('an %s game for the Nintendo Switch', $categoryName);
                break;
            case 3:
                $blurbText = sprintf('a %s for the Nintendo Switch', $categoryName);
                break;
            case 4:
                $blurbText = sprintf('an %s for the Nintendo Switch', $categoryName);
                break;
            case 5:
                $blurbText = sprintf('involves %s for the Nintendo Switch', $categoryName);
                break;
            default:
                $blurbText = '';
        }

        return $blurbText;
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        return Category::orderBy('name', 'asc')->get();
    }

    public function getAllWithoutParents()
    {
        return Category::whereDoesntHave('parent')->orderBy('name', 'asc')->get();
    }

    public function getByName($name)
    {
        return Category::where('name', $name)->first();
    }

    public function getByLinkTitle($linkTitle)
    {
        return Category::where('link_title', $linkTitle)->first();
    }

    /**
     * @param $categoryId
     * @return mixed
     */
    public function countReleasedByCategory($categoryId)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('games.category_id', $categoryId);

        $games = $games->count();

        return $games;
    }

    /**
     * @param $categoryId
     * @param int $limit
     * @return mixed
     */
    public function getRankedByCategory($categoryId, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('games.category_id', $categoryId)
            ->whereNotNull('games.game_rank')
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * @param $categoryId
     * @param int $limit
     * @return mixed
     */
    public function getUnrankedByCategory($categoryId, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('games.category_id', $categoryId)
            ->whereNull('games.game_rank')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

}