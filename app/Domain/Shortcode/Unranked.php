<?php


namespace App\Domain\Shortcode;

use App\Domain\TopRated\DbQueries as DbTopRated;

class Unranked
{
    private $matches = [
        // 2018
        ['key' => '[unranked-2018-01]', 'year' => '2018', 'month' => '01',],
        ['key' => '[unranked-2018-02]', 'year' => '2018', 'month' => '02',],
        ['key' => '[unranked-2018-03]', 'year' => '2018', 'month' => '03',],
        ['key' => '[unranked-2018-04]', 'year' => '2018', 'month' => '04',],
        ['key' => '[unranked-2018-05]', 'year' => '2018', 'month' => '05',],
        ['key' => '[unranked-2018-06]', 'year' => '2018', 'month' => '06',],
        ['key' => '[unranked-2018-07]', 'year' => '2018', 'month' => '07',],
        ['key' => '[unranked-2018-08]', 'year' => '2018', 'month' => '08',],
        ['key' => '[unranked-2018-09]', 'year' => '2018', 'month' => '09',],
        ['key' => '[unranked-2018-10]', 'year' => '2018', 'month' => '10',],
        ['key' => '[unranked-2018-11]', 'year' => '2018', 'month' => '11',],
        ['key' => '[unranked-2018-12]', 'year' => '2018', 'month' => '12',],
        // 2019
        ['key' => '[unranked-2019-01]', 'year' => '2019', 'month' => '01',],
        ['key' => '[unranked-2019-02]', 'year' => '2019', 'month' => '02',],
        ['key' => '[unranked-2019-03]', 'year' => '2019', 'month' => '03',],
        ['key' => '[unranked-2019-04]', 'year' => '2019', 'month' => '04',],
        ['key' => '[unranked-2019-05]', 'year' => '2019', 'month' => '05',],
        ['key' => '[unranked-2019-06]', 'year' => '2019', 'month' => '06',],
        ['key' => '[unranked-2019-07]', 'year' => '2019', 'month' => '07',],
        ['key' => '[unranked-2019-08]', 'year' => '2019', 'month' => '08',],
        ['key' => '[unranked-2019-09]', 'year' => '2019', 'month' => '09',],
        ['key' => '[unranked-2019-10]', 'year' => '2019', 'month' => '10',],
        ['key' => '[unranked-2019-11]', 'year' => '2019', 'month' => '11',],
        ['key' => '[unranked-2019-12]', 'year' => '2019', 'month' => '12',],
    ];

    /**
     * @var DbTopRated
     */
    private $dbTopRated;

    private $html;

    public function __construct($html)
    {
        $this->dbTopRated = new DbTopRated();
        $this->html = $html;
    }

    public function parseShortcodes()
    {
        $parsedHtml = $this->html;

        foreach ($this->matches as $shortcode) {

            $key = $shortcode['key'];
            $year = $shortcode['year'];
            $month = $shortcode['month'];

            if (strpos($parsedHtml, $key)) {
                $gameList = $this->dbTopRated->byMonthUnranked($year, $month);
                $shortcodeHtml = view('ui.blocks.shortcodes.unranked', ['GameList' => $gameList]);
                $parsedHtml = str_replace($key, $shortcodeHtml, $parsedHtml);
            }

        }

        return $parsedHtml;
    }
}