<?php


namespace App\Domain\ViewBreadcrumbs;


class Staff extends Base
{
    private $toastedCrumbs = [];

    public function __construct()
    {
        $this->toastedCrumbs['games.dashboard'] = ['url' => route('staff.games.dashboard'), 'text' => 'Games'];

        $this->toastedCrumbs['reviews.dashboard'] = ['url' => route('staff.reviews.dashboard'), 'text' => 'Reviews'];
        $this->toastedCrumbs['reviews.reviewSites'] = ['url' => route('staff.reviews.reviewSites.index'), 'text' => 'Review sites'];
        $this->toastedCrumbs['reviews.feedLinks'] = ['url' => route('staff.reviews.feedLinks.index'), 'text' => 'Feed links'];
        $this->toastedCrumbs['reviews.reviewDrafts'] = ['url' => route('staff.reviews.review-drafts.showPending'), 'text' => 'Review drafts'];

        $this->toastedCrumbs['categorisation.dashboard'] = ['url' => route('staff.categorisation.dashboard'), 'text' => 'Categorisation'];
        $this->toastedCrumbs['categorisation.category.list'] = ['url' => route('staff.categorisation.category.list'), 'text' => 'Categories'];
        $this->toastedCrumbs['categorisation.tag.list'] = ['url' => route('staff.categorisation.tag.list'), 'text' => 'Tags'];
        $this->toastedCrumbs['categorisation.series.list'] = ['url' => route('staff.categorisation.game-series.list'), 'text' => 'Series'];
        $this->toastedCrumbs['categorisation.collection.list'] = ['url' => route('staff.categorisation.game-collection.list'), 'text' => 'Collections'];

        $this->toastedCrumbs['news.dashboard'] = ['url' => route('staff.news.dashboard'), 'text' => 'News'];
        $this->toastedCrumbs['news.list'] = ['url' => route('staff.news.list'), 'text' => 'News list'];
        $this->toastedCrumbs['news.categories'] = ['url' => route('staff.news.category.list'), 'text' => 'News categories'];

        $this->toastedCrumbs['partners.dashboard'] = ['url' => route('staff.partners.dashboard'), 'text' => 'Partners'];

        $this->toastedCrumbs['dataSources.dashboard'] = ['url' => route('staff.data-sources.dashboard'), 'text' => 'Data sources'];
        $this->toastedCrumbs['dataSources.nintendoCoUk.unlinked'] = ['url' => route('staff.data-sources.nintendo-co-uk.unlinked'), 'text' => 'Nintendo.co.uk API - Unlinked items'];
        $this->toastedCrumbs['dataSources.wikipedia.unlinked'] = ['url' => route('staff.data-sources.wikipedia.unlinked'), 'text' => 'Wikipedia - Unlinked items'];

        $this->toastedCrumbs['dataQuality.dashboard'] = ['url' => route('staff.data-quality.dashboard'), 'text' => 'Data quality'];
        $this->toastedCrumbs['dataQuality.categories.dashboard'] = ['url' => route('staff.data-quality.category.dashboard'), 'text' => 'Categories'];

        $this->toastedCrumbs['staff.inviteCodesList'] = ['url' => route('staff.invite-code.list'), 'text' => 'Invite codes'];

        $this->toastedCrumbs['owner.auditList'] = ['url' => route('owner.audit.index'), 'text' => 'Audit'];
        $this->toastedCrumbs['owner.usersList'] = ['url' => route('owner.user.list'), 'text' => 'Users'];
        $this->toastedCrumbs['owner.statsDashboard'] = ['url' => route('staff.stats.dashboard'), 'text' => 'Stats'];
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

    public function reviewsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['reviews.dashboard'])->addTitleAndReturn($pageTitle);
    }

    public function reviewsReviewSitesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['reviews.dashboard'])
            ->addCrumb($this->toastedCrumbs['reviews.reviewSites'])
            ->addTitleAndReturn($pageTitle);
    }

    public function reviewsFeedLinksSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['reviews.dashboard'])
            ->addCrumb($this->toastedCrumbs['reviews.feedLinks'])
            ->addTitleAndReturn($pageTitle);
    }

    public function reviewsReviewDraftsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['reviews.dashboard'])
                    ->addCrumb($this->toastedCrumbs['reviews.reviewDrafts'])
                    ->addTitleAndReturn($pageTitle);
    }

    public function categorisationSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['categorisation.dashboard'])->addTitleAndReturn($pageTitle);
    }

    public function categorisationCategoriesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['categorisation.dashboard'])
                    ->addCrumb($this->toastedCrumbs['categorisation.category.list'])
                    ->addTitleAndReturn($pageTitle);
    }

    public function categorisationTagsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['categorisation.dashboard'])
                    ->addCrumb($this->toastedCrumbs['categorisation.tag.list'])
                    ->addTitleAndReturn($pageTitle);
    }

    public function categorisationSeriesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['categorisation.dashboard'])
                    ->addCrumb($this->toastedCrumbs['categorisation.series.list'])
                    ->addTitleAndReturn($pageTitle);
    }

    public function categorisationCollectionsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['categorisation.dashboard'])
                    ->addCrumb($this->toastedCrumbs['categorisation.collection.list'])
                    ->addTitleAndReturn($pageTitle);
    }

    public function newsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['news.dashboard'])->addTitleAndReturn($pageTitle);
    }

    public function newsListSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['news.dashboard'])
                    ->addCrumb($this->toastedCrumbs['news.list'])
                    ->addTitleAndReturn($pageTitle);
    }

    public function newsCategoriesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['news.dashboard'])
                    ->addCrumb($this->toastedCrumbs['news.categories'])
                    ->addTitleAndReturn($pageTitle);
    }

    public function dataSourcesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['dataSources.dashboard'])->addTitleAndReturn($pageTitle);
    }

    public function dataSourcesNintendoCoUkUnlinkedSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['dataSources.dashboard'])
                    ->addCrumb($this->toastedCrumbs['dataSources.nintendoCoUk.unlinked'])
                    ->addTitleAndReturn($pageTitle);
    }

    public function dataSourcesWikipediaUnlinkedSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['dataSources.dashboard'])
                    ->addCrumb($this->toastedCrumbs['dataSources.wikipedia.unlinked'])
                    ->addTitleAndReturn($pageTitle);
    }

    public function dataQualitySubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['dataQuality.dashboard'])->addTitleAndReturn($pageTitle);
    }

    public function dataQualityCategoriesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['dataQuality.dashboard'])
                    ->addCrumb($this->toastedCrumbs['dataQuality.categories.dashboard'])
                    ->addTitleAndReturn($pageTitle);
    }

    // *** Invite codes *** //

    public function inviteCodesSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['staff.inviteCodesList'])->addTitleAndReturn($pageTitle);
    }

    // *** Owner pages *** //

    public function auditSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['owner.auditList'])->addTitleAndReturn($pageTitle);
    }

    public function usersSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['owner.usersList'])->addTitleAndReturn($pageTitle);
    }

    public function statsSubpage($pageTitle)
    {
        return $this->addCrumb($this->toastedCrumbs['owner.statsDashboard'])->addTitleAndReturn($pageTitle);
    }
}