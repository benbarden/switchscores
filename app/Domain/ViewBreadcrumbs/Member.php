<?php


namespace App\Domain\ViewBreadcrumbs;

class Member extends Base
{
    private $toastedCrumbs = [];

    public function __construct()
    {
        $this->toastedCrumbs['member.dashboard'] = ['url' => route('user.index'), 'text' => 'Members'];

        $this->toastedCrumbs['member.collection.landing'] = ['url' => route('user.collection.landing'), 'text' => 'Games collection'];
    }

    public function topLevelPage($pageTitle)
    {
        return $this->addTitleAndReturn($pageTitle);
    }

    // *** Member pages *** //

    public function collectionSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['member.collection.landing'])->addTitleAndReturn($pageTitle);
    }

}