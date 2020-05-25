<?php


namespace App\Services\Shortcode;

use App\Services\TopRatedService;

class TopRated
{
    private $matches = [
        // 2018
        ['key' => '[top-rated-2018-01]', 'year' => '2018', 'month' => '01',],
        ['key' => '[top-rated-2018-02]', 'year' => '2018', 'month' => '02',],
        ['key' => '[top-rated-2018-03]', 'year' => '2018', 'month' => '03',],
        ['key' => '[top-rated-2018-04]', 'year' => '2018', 'month' => '04',],
        ['key' => '[top-rated-2018-05]', 'year' => '2018', 'month' => '05',],
        ['key' => '[top-rated-2018-06]', 'year' => '2018', 'month' => '06',],
        ['key' => '[top-rated-2018-07]', 'year' => '2018', 'month' => '07',],
        ['key' => '[top-rated-2018-08]', 'year' => '2018', 'month' => '08',],
        ['key' => '[top-rated-2018-09]', 'year' => '2018', 'month' => '09',],
        ['key' => '[top-rated-2018-10]', 'year' => '2018', 'month' => '10',],
        ['key' => '[top-rated-2018-11]', 'year' => '2018', 'month' => '11',],
        ['key' => '[top-rated-2018-12]', 'year' => '2018', 'month' => '12',],
        // 2019
        ['key' => '[top-rated-2019-01]', 'year' => '2019', 'month' => '01',],
        ['key' => '[top-rated-2019-02]', 'year' => '2019', 'month' => '02',],
        ['key' => '[top-rated-2019-03]', 'year' => '2019', 'month' => '03',],
        ['key' => '[top-rated-2019-04]', 'year' => '2019', 'month' => '04',],
        ['key' => '[top-rated-2019-05]', 'year' => '2019', 'month' => '05',],
        ['key' => '[top-rated-2019-06]', 'year' => '2019', 'month' => '06',],
        ['key' => '[top-rated-2019-07]', 'year' => '2019', 'month' => '07',],
        ['key' => '[top-rated-2019-08]', 'year' => '2019', 'month' => '08',],
        ['key' => '[top-rated-2019-09]', 'year' => '2019', 'month' => '09',],
        ['key' => '[top-rated-2019-10]', 'year' => '2019', 'month' => '10',],
        ['key' => '[top-rated-2019-11]', 'year' => '2019', 'month' => '11',],
        ['key' => '[top-rated-2019-12]', 'year' => '2019', 'month' => '12',],
    ];

    /**
     * @var TopRatedService
     */
    private $serviceTopRated;

    private $html;

    public function __construct($serviceTopRated, $html)
    {
        $this->serviceTopRated = $serviceTopRated;
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
                $gameList = $this->serviceTopRated->getByMonthWithRanks($year, $month, 10);
                $shortcodeHtml = view('modules.shortcodes.top-rated', ['GameList' => $gameList]);
                $parsedHtml = str_replace($key, $shortcodeHtml, $parsedHtml);
            }

        }

        return $parsedHtml;
    }
}