<?php


namespace App\Domain\Shortcode;

use Illuminate\Support\Collection;

use App\Domain\Game\Repository as GameRepository;

class GameHeader
{
    private $pattern = "/\[gameheader (.+?)\]/";

    /**
     * @var GameRepository
     */
    private $repoGame;

    /**
     * @var Collection
     */
    private $seedGamesCollection;

    private $html;

    public function __construct($html)
    {
        $this->html = $html;

        $this->repoGame = new GameRepository();
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
                    $gameList = $this->repoGame->getByIdList($idList, ['rating_avg', 'desc']);
                }

                $bindings['GameList'] = $gameList;
                $shortcodeHtml = view('ui.blocks.shortcodes.game-header', $bindings);
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