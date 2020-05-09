<?php

namespace App\Services\ViewHelper;

class Breadcrumbs
{
    private $breadcrumbs = [];

    const KEY_URL = 'url';
    const KEY_TEXT = 'text';

    // ***** Standard methods ***** //

    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    private function addCrumb($crumbItem)
    {
        array_push($this->breadcrumbs, $crumbItem);
        return $this;
    }

    public function addPageTitle($pageTitle)
    {
        $crumbItem = ['text' => $pageTitle];
        return $this->addCrumb($crumbItem);
    }

    // ***** Games ***** //

    public function addGamesDashboard()
    {
        $crumbItem = ['url' => route('staff.games.dashboard'), 'text' => 'Games'];
        return $this->addCrumb($crumbItem);
    }

    public function makeGamesSubPage($pageTitle)
    {
        return $this->addGamesDashboard()
                    ->addPageTitle($pageTitle)
                    ->getBreadcrumbs();
    }

}