<?php


namespace App\Domain\ViewBreadcrumbs;


class MainSite extends Base
{
    private $toastedCrumbs = [];

    public function __construct()
    {
        $this->toastedCrumbs['gamesLanding'] = ['url' => route('games.landing'), 'text' => 'Games'];
        $this->toastedCrumbs['listsLanding'] = ['url' => route('lists.landing'), 'text' => 'Lists'];
        $this->toastedCrumbs['topRatedLanding'] = ['url' => route('topRated.landing'), 'text' => 'Top Rated'];
        $this->toastedCrumbs['reviewsLanding'] = ['url' => route('reviews.landing'), 'text' => 'Reviews'];
        $this->toastedCrumbs['partnersLanding'] = ['url' => route('partners.landing'), 'text' => 'Partners'];
        $this->toastedCrumbs['newsLanding'] = ['url' => route('news.landing'), 'text' => 'News'];
        $this->toastedCrumbs['aboutLanding'] = ['url' => route('about.landing'), 'text' => 'About'];

        $this->toastedCrumbs['gamesByTitleLanding'] = ['url' => route('games.browse.byTitle.landing'), 'text' => 'By title'];
        $this->toastedCrumbs['gamesByDateLanding'] = ['url' => route('games.browse.byDate.landing'), 'text' => 'By date'];
        $this->toastedCrumbs['gamesByCategoryLanding'] = ['url' => route('games.browse.byCategory.landing'), 'text' => 'By category'];
        $this->toastedCrumbs['gamesByTagLanding'] = ['url' => route('games.browse.byTag.landing'), 'text' => 'By tag'];
        $this->toastedCrumbs['gamesBySeriesLanding'] = ['url' => route('games.browse.bySeries.landing'), 'text' => 'By series'];
        $this->toastedCrumbs['gamesByCollectionLanding'] = ['url' => route('games.browse.byCollection.landing'), 'text' => 'By collection'];
    }

    public function topLevelPage($pageTitle)
    {
        return $this->addPageTitle($pageTitle)->getBreadcrumbs();
    }

    public function listsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['listsLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function gamesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['gamesLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function gamesByTitleSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['gamesLanding'])
            ->addCrumb($this->toastedCrumbs['gamesByTitleLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function gamesByDateSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['gamesLanding'])
            ->addCrumb($this->toastedCrumbs['gamesByDateLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function gamesBySubcategorySubpage($parentCategory, $pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['gamesLanding'])
            ->addCrumb($this->toastedCrumbs['gamesByCategoryLanding'])
            ->addCrumb(
                [
                    'url' => route('games.browse.byCategory.page', ['category' => $parentCategory->link_title]),
                    'text' => $parentCategory->name
                ])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function gamesByCategorySubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['gamesLanding'])
            ->addCrumb($this->toastedCrumbs['gamesByCategoryLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function gamesByTagSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['gamesLanding'])
            ->addCrumb($this->toastedCrumbs['gamesByTagLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function gamesBySeriesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['gamesLanding'])
            ->addCrumb($this->toastedCrumbs['gamesBySeriesLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function gamesByCollectionSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['gamesLanding'])
            ->addCrumb($this->toastedCrumbs['gamesByCollectionLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function topRatedSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['topRatedLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function reviewsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['reviewsLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function partnersSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['partnersLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function newsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['newsLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function aboutSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['aboutLanding'])
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }
}