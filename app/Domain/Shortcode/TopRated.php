<?php


namespace App\Domain\Shortcode;

use App\Domain\TopRated\DbQueries as DbTopRated;

class TopRated
{
    private $matches;

    /**
     * @var DbTopRated
     */
    private $dbTopRated;

    private $html;

    public function __construct($html)
    {
        $this->dbTopRated = new DbTopRated();
        $this->html = $html;
        $this->generateMatches();
    }

    public function generateMatches()
    {
        $matchYears = [2018, 2019, 2020, 2021, 2022];
        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

        foreach ($matchYears as $year) {

            foreach ($months as $month) {

                $matchKey = sprintf('[top-rated-%s-%s]', $year, $month);
                $matchArray = ['key' => $matchKey, 'year' => $year, 'month' => $month];
                $this->matches[] = $matchArray;

            }

        }
    }

    public function parseShortcodes()
    {
        $parsedHtml = $this->html;

        foreach ($this->matches as $shortcode) {

            $key = $shortcode['key'];
            $year = $shortcode['year'];
            $month = $shortcode['month'];

            if (strpos($parsedHtml, $key)) {
                // @todo either replace the console with a variable, or remove this shortcode
                $gameList = $this->dbTopRated->byMonthWithRanks(1, $year, $month, 10);
                $shortcodeHtml = view('ui.blocks.shortcodes.top-rated', ['GameList' => $gameList]);
                $parsedHtml = str_replace($key, $shortcodeHtml, $parsedHtml);
            }

        }

        return $parsedHtml;
    }
}