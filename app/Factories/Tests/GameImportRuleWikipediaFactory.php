<?php


namespace App\Factories\Tests;

use App\Models\GameImportRuleWikipedia;

class GameImportRuleWikipediaFactory
{
    public static function makeWithAllEnabled()
    {
        $gameImportRule = new GameImportRuleWikipedia([
            'ignore_developers' => '1',
            'ignore_publishers' => '1',
            'ignore_europe_dates' => '1',
            'ignore_us_dates' => '1',
            'ignore_jp_dates' => '1',
        ]);

        return $gameImportRule;
    }
}