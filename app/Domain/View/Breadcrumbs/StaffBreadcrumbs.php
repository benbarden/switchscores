<?php

namespace App\Domain\View\Breadcrumbs;

use App\Models\DataSource;
use App\Models\Game;

final class StaffBreadcrumbs
{
    // *** MAIN DASHBOARD *** //
    public static function staffDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Dashboard'),
        ]);
    }

    // *** GENERIC TOP LEVEL PAGES *** //
    // Only use for standalone pages - not dashboards with child pages.
    public static function staffGenericTopLevel(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** GAMES *** //
    public static function gamesDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Games dashboard'),
        ]);
    }

    public static function gamesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Games', route('staff.games.dashboard')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function gamesDetailSubpage(string $title, Game $game): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Games', route('staff.games.dashboard')),
            new BreadcrumbItem($game->title, route('staff.games.detail', ['gameId' => $game->id])),
            new BreadcrumbItem($title),
        ]);
    }

    // *** REVIEWS *** //
    public static function reviewsDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews dashboard'),
        ]);
    }

    public static function reviewsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews', route('staff.reviews.dashboard')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function reviewsReviewDraftsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews', route('staff.reviews.dashboard')),
            new BreadcrumbItem('Review drafts', route('staff.reviews.review-drafts.showPending')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function reviewsQuickReviewsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews', route('staff.reviews.dashboard')),
            new BreadcrumbItem('Quick reviews', route('staff.reviews.quick-reviews.list')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function reviewsReviewSitesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews', route('staff.reviews.dashboard')),
            new BreadcrumbItem('Review sites', route('staff.reviews.reviewSites.index')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function reviewsFeedLinksSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews', route('staff.reviews.dashboard')),
            new BreadcrumbItem('Feed links', route('staff.reviews.feedLinks.index')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function reviewsReviewLinksSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews', route('staff.reviews.dashboard')),
            new BreadcrumbItem('Review links', route('staff.reviews.link.list')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function reviewsUnrankedByReviewCountSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews', route('staff.reviews.dashboard')),
            new BreadcrumbItem('Unranked: By review count', route('staff.reviews.unranked.review-count-landing')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function reviewsUnrankedByReleaseYearSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews', route('staff.reviews.dashboard')),
            new BreadcrumbItem('Unranked: By release year', route('staff.reviews.unranked.release-year-landing')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function reviewsCampaignsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Reviews', route('staff.reviews.dashboard')),
            new BreadcrumbItem('Review campaigns', route('staff.reviews.campaigns')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** CATEGORISATION *** //
    public static function categorisationDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Categorisation dashboard'),
        ]);
    }

    public static function categorisationSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Categorisation', route('staff.categorisation.dashboard')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function categorisationCategoriesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Categorisation', route('staff.categorisation.dashboard')),
            new BreadcrumbItem('Categories', route('staff.categorisation.category.list')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function categorisationTagsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Categorisation', route('staff.categorisation.dashboard')),
            new BreadcrumbItem('Tags', route('staff.categorisation.tag.list')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function categorisationSeriesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Categorisation', route('staff.categorisation.dashboard')),
            new BreadcrumbItem('Series', route('staff.categorisation.game-series.list')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function categorisationCollectionsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Categorisation', route('staff.categorisation.dashboard')),
            new BreadcrumbItem('Collections', route('staff.categorisation.game-collection.list')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** NEWS *** //

    public static function newsDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('News dashboard'),
        ]);
    }
    public static function newsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('News', route('staff.news.dashboard')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function newsListSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('News', route('staff.news.dashboard')),
            new BreadcrumbItem('News list', route('staff.news.list')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function newsCategoriesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('News', route('staff.news.dashboard')),
            new BreadcrumbItem('News categories', route('staff.news.category.list')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** GAMES COMPANIES *** //

    public static function gamesCompaniesDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Games companies dashboard'),
        ]);
    }

    public static function gamesCompaniesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Games companies', route('staff.games-companies.dashboard')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function gamesCompaniesListSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Games companies', route('staff.games-companies.dashboard')),
            new BreadcrumbItem('Company search', route('staff.games-companies.search')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function gamesCompaniesOutreachSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Games companies', route('staff.games-companies.dashboard')),
            new BreadcrumbItem('Partner outreach', route('staff.partners.outreach.list')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** DATA SOURCES *** //

    public static function dataSourcesDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Data sources dashboard'),
        ]);
    }

    public static function dataSourcesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Data sources', route('staff.data-sources.dashboard')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function dataSourcesNintendoUnlinkedSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Data sources', route('staff.data-sources.dashboard')),
            new BreadcrumbItem('Nintendo.co.uk API - Unlinked items', route('staff.data-sources.nintendo-co-uk.unlinked')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function dataSourcesListRawSubpage(string $title, DataSource $dataSource): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Data sources', route('staff.data-sources.dashboard')),
            new BreadcrumbItem($dataSource->name.' - Raw items', route('staff.data-sources.list-raw', ['sourceId' => $dataSource->id])),
            new BreadcrumbItem($title),
        ]);
    }

    // *** DATA QUALITY *** //

    public static function dataQualityDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Data quality'),
        ]);
    }

    public static function dataQualitySubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Data quality', route('staff.data-quality.dashboard')),
            new BreadcrumbItem($title),
        ]);
    }

    public static function dataQualityCategoriesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Data quality', route('staff.data-quality.dashboard')),
            new BreadcrumbItem('Categories', route('staff.data-quality.category.dashboard')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** INSIGHTS *** //
    public static function insightsDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Insights'),
        ]);
    }

    // *** TOOLS HUB *** //
    public static function toolsHub(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Tools hub'),
        ]);
    }

    // *** INVITE CODES *** //
    public static function inviteCodesDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Invite codes'),
        ]);
    }

    public static function inviteCodesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Invite codes', route('staff.invite-code.list')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** OWNER: USERS *** //
    public static function usersList(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Users'),
        ]);
    }

    public static function usersSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Users', route('owner.user.list')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** OWNER: STATS *** //
    public static function statsDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Stats dashboard'),
        ]);
    }

    public static function statsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Stats', route('staff.stats.dashboard')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** OWNER: AUDIT *** //
    public static function auditDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Audit'),
        ]);
    }

    public static function auditSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Staff', route('staff.index')),
            new BreadcrumbItem('Audit', route('owner.audit.index')),
            new BreadcrumbItem($title),
        ]);
    }

}