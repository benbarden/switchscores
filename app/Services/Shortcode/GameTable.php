<?php


namespace App\Services\Shortcode;

use App\Services\GameService;

class GameTable
{
    private $pattern = "/\[gametable (.+?)\]/";

    /**
     * @var GameService
     */
    private $serviceGame;

    /**
     * @var array
     */
    private $seedGames;

    private $html;

    public function __construct($html, $serviceGame = null, $seedGames = [])
    {
        $this->html = $html;

        if ($serviceGame) {
            $this->serviceGame = $serviceGame;
        }

        if ($seedGames) {
            $this->seedGames = $seedGames;
        }
    }

    public function parseCode($matches)
    {
        // parse out the arguments
        $args = explode(" ", $matches[2]);
        $params = [];
        foreach ($args as $arg) {
            list($opt, $val) = explode("=", $arg);
            $params[$opt] = trim($val, '"');
        }

        $bindings = [];
        switch ($matches[1]) {
            case "gametable":
                $idList = $params['ids'];
                if ($this->seedGames) {
                    $gameList = $this->seedGames;
                } else {
                    $gameList = $this->serviceGame->getByIdList($idList, ['rating_avg', 'desc']);
                }

                $bindings['GameList'] = $gameList;
                $shortcodeHtml = view('modules.shortcodes.game-table', $bindings);
                return $shortcodeHtml;
                break;
        }

    }

    public function parseShortcodes()
    {
        $sourceHtml = $this->html;

        $parsedHtml = preg_replace_callback("/\[(\w+) (.+?)]/", array($this, 'parseCode'), $sourceHtml);

        return $parsedHtml;
    }
}