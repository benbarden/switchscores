<?php

namespace App\Domain\Category;

use App\Models\Category;
use App\Models\Console;

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

    public function parse(Category $category, Console $console)
    {
        // Only convert if it's not an acronym
        if ($category->name == strtoupper($category->name)) {
            $categoryName = $category->name;
        } else {
            $categoryName = strtolower($category->name);
        }

        // Get console text
        if ($console->id == Console::ID_SWITCH_1) {
            $consoleText = 'for the Nintendo Switch 1';
        } elseif ($console->id == Console::ID_SWITCH_2) {
            $consoleText = 'for the Nintendo Switch 2';
        } else {
            $consoleText = 'for an unknown console';
        }

        switch ($category->blurb_option) {
            case Category::BLURB_NONE:
                $blurbText = '';
                break;
            case Category::BLURB_A_XX_GAME:
                $blurbText = sprintf('a %s game %s', $categoryName, $consoleText);
                break;
            case Category::BLURB_AN_XX_GAME:
                $blurbText = sprintf('an %s game %s', $categoryName, $consoleText);
                break;
            case Category::BLURB_A_XX:
                $blurbText = sprintf('a %s %s', $categoryName, $consoleText);
                break;
            case Category::BLURB_AN_XX:
                $blurbText = sprintf('an %s %s', $categoryName, $consoleText);
                break;
            case Category::BLURB_INVOLVES_XX:
                $blurbText = sprintf('involves %s %s', $categoryName, $consoleText);
                break;
            default:
                $blurbText = '';
        }

        return $blurbText;
    }
}