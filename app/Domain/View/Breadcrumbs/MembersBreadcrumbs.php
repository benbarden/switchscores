<?php

namespace App\Domain\View\Breadcrumbs;

final class MembersBreadcrumbs
{
    // *** MAIN DASHBOARD *** //
    public static function membersDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem('Dashboard'),
        ]);
    }

    // *** GENERIC TOP LEVEL PAGES *** //
    // Only use for standalone pages - not dashboards with child pages.
    public static function membersGenericTopLevel(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** GAMES COLLECTION *** //
    public static function collectionSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem('Games collection', route('members.collection.landing')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** QUICK REVIEWS *** //
    public static function quickReviewsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem('Quick reviews', route('members.quick-reviews.list')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** DEVELOPERS *** //
    public static function developersDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem('Developers'),
        ]);
    }

    public static function developersSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem('Developers', route('members.developers.index')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** GAMES COMPANIES *** //
    public static function gamesCompaniesDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem('Games company dashboard'),
        ]);
    }

    public static function gamesCompaniesSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem('Games companies', route('games-companies.index')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** REVIEWERS *** //
    public static function reviewersDashboard(): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem('Reviewers'),
        ]);
    }

    public static function reviewersSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Members', route('members.index')),
            new BreadcrumbItem('Reviewers', route('reviewers.index')),
            new BreadcrumbItem($title),
        ]);
    }

}