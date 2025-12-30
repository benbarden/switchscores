<?php


namespace App\Domain\ViewBreadcrumbs;

use App\Models\DataSource;
use App\Models\Game;

/**
 * @deprecated
 */
class Staff extends Base
{
    private $toastedCrumbs = [];

    public function __construct()
    {
        $this->toastedCrumbs['games.dashboard'] = ['url' => route('staff.games.dashboard'), 'text' => 'Games'];
    }

    public function topLevelPage($pageTitle)
    {
        return $this->addTitleAndReturn($pageTitle);
    }

    // *** Staff pages *** //

    public function gamesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['games.dashboard'])->addTitleAndReturn($pageTitle);
    }

    public function gamesDetailSubpage($pageTitle, Game $game)
    {
        $gamesDetailCrumb = [
            'url' => route('staff.games.detail', ['gameId' => $game->id]),
            'text' => $game->title
        ];
        return $this->addCrumb($this->toastedCrumbs['games.dashboard'])
            ->addCrumb($gamesDetailCrumb)
            ->addTitleAndReturn($pageTitle);
    }
}