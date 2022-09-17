<?php


namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    const BLURB_NONE = 0;
    const BLURB_A_XX_GAME = 1;
    const BLURB_AN_XX_GAME = 2;
    const BLURB_A_XX = 3;
    const BLURB_AN_XX = 4;
    const BLURB_INVOLVES_XX = 5;

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
            self::BLURB_NONE => 'None',
            self::BLURB_A_XX_GAME => 'a (category) game',
            self::BLURB_AN_XX_GAME => 'an (category) game',
            self::BLURB_A_XX => 'a (category)',
            self::BLURB_AN_XX => 'an (category)',
            self::BLURB_INVOLVES_XX => 'involves (category)',
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
            case self::BLURB_NONE:
                $blurbText = '';
                break;
            case self::BLURB_A_XX_GAME:
                $blurbText = sprintf('a %s game for the Nintendo Switch', $categoryName);
                break;
            case self::BLURB_AN_XX_GAME:
                $blurbText = sprintf('an %s game for the Nintendo Switch', $categoryName);
                break;
            case self::BLURB_A_XX:
                $blurbText = sprintf('a %s for the Nintendo Switch', $categoryName);
                break;
            case self::BLURB_AN_XX:
                $blurbText = sprintf('an %s for the Nintendo Switch', $categoryName);
                break;
            case self::BLURB_INVOLVES_XX:
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

}