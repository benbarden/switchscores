<?php


namespace App\Domain\Shortcode;

use Illuminate\Support\Collection;
use App\Services\GameService;

class GameHeader
{
    private $pattern = "/\[gameheader (.+?)\]/";

    /**
     * @var GameService
     */
    private $serviceGame;

    /**
     * @var Collection
     */
    private $seedGamesCollection;

    private $html;

    public function __construct($html, $serviceGame = null)
    {
        $this->html = $html;

        if ($serviceGame) {
            $this->serviceGame = $serviceGame;
        }
    }

    public function setSeedGames(Collection $seedGames)
    {
        $this->seedGamesCollection = $seedGames;
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
            case "gameheader":
                $idList = $params['ids'];
                if ($this->seedGamesCollection) {
                    $gameList = $this->seedGamesCollection;
                } else {
                    $gameList = $this->serviceGame->getByIdList($idList, ['rating_avg', 'desc']);
                }

                $bindings['GameList'] = $gameList;
                $shortcodeHtml = view('modules.shortcodes.game-header', $bindings);
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