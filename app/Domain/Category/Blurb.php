<?php

namespace App\Domain\Category;

use App\Models\Category;

class Blurb
{
    public function getOptions()
    {
        $options = [
            Category::BLURB_NONE => 'None',
            Category::BLURB_A_XX_GAME => 'a (category) game',
            Category::BLURB_AN_XX_GAME => 'an (category) game',
            Category::BLURB_A_XX => 'a (category)',
            Category::BLURB_AN_XX => 'an (category)',
            Category::BLURB_INVOLVES_XX => 'involves (category)',
        ];

        return $options;
    }

    public function default()
    {
        return 'a game for the Nintendo Switch';
    }

    public function parse(Category $category)
    {
        // Only convert if it's not an acronym
        if ($category->name == strtoupper($category->name)) {
            $categoryName = $category->name;
        } else {
            $categoryName = strtolower($category->name);
        }

        switch ($category->blurb_option) {
            case Category::BLURB_NONE:
                $blurbText = '';
                break;
            case Category::BLURB_A_XX_GAME:
                $blurbText = sprintf('a %s game for the Nintendo Switch', $categoryName);
                break;
            case Category::BLURB_AN_XX_GAME:
                $blurbText = sprintf('an %s game for the Nintendo Switch', $categoryName);
                break;
            case Category::BLURB_A_XX:
                $blurbText = sprintf('a %s for the Nintendo Switch', $categoryName);
                break;
            case Category::BLURB_AN_XX:
                $blurbText = sprintf('an %s for the Nintendo Switch', $categoryName);
                break;
            case Category::BLURB_INVOLVES_XX:
                $blurbText = sprintf('involves %s for the Nintendo Switch', $categoryName);
                break;
            default:
                $blurbText = '';
        }

        return $blurbText;
    }
}