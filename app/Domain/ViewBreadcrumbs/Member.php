<?php


namespace App\Domain\ViewBreadcrumbs;

class Member extends Base
{
    private $toastedCrumbs = [];

    public function __construct()
    {
        $this->toastedCrumbs['member.dashboard'] = ['url' => route('user.index'), 'text' => 'Members'];

        $this->toastedCrumbs['member.collection.landing'] = ['url' => route('user.collection.landing'), 'text' => 'Games collection'];

        $this->toastedCrumbs['member.quickReviews.list'] = ['url' => route('user.quick-reviews.list'), 'text' => 'Quick reviews'];

        $this->toastedCrumbs['member.developers.index'] = ['url' => route('user.developers.index'), 'text' => 'Developers'];
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

    public function quickReviewsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['member.quickReviews.list'])->addTitleAndReturn($pageTitle);
    }

    public function developersSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['member.developers.index'])->addTitleAndReturn($pageTitle);
    }
}