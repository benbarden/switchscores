<?php

namespace App\Domain\View\Breadcrumbs;

use App\Models\Category;
use App\Models\Console;
use App\Models\Game;

final class PublicBreadcrumbs
{
    // *** GENERIC TOP LEVEL PAGES *** //
    // Only use for standalone pages - not sections with child pages.
    public static function topLevel(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** GAMES *** //
    public static function game(Game $game): BreadcrumbNav
    {
        return self::consoleSubpage($game->title, $game->console);
    }

    // *** CONSOLE *** //
    public static function console(Console $console): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name),
        ]);
    }

    public static function consoleSubpage(string $title, Console $console): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem($title),
        ]);
    }

    public static function consoleYearPage(string $title, Console $console): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem('By date', route('console.byDate', ['console' => $console])),
            new BreadcrumbItem($title),
        ]);
    }

    public static function consoleYearMonthPage(string $title, Console $console, $year): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem('By date', route('console.byDate', ['console' => $console])),
            new BreadcrumbItem($year, route('console.byYear', ['console' => $console, 'year' => $year])),
            new BreadcrumbItem($title),
        ]);
    }

    public static function consoleCategoryPage(string $title, Console $console, ?Category $parentCategory = null): BreadcrumbNav
    {
        return new BreadcrumbNav(array_values(array_filter([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem('By category', route('console.byCategory.landing', ['console' => $console])),
            $parentCategory ? new BreadcrumbItem($parentCategory->name, route('console.byCategory.page', ['console' => $console, 'category' => $parentCategory->link_title])) : null,
            new BreadcrumbItem($title),
        ])));
    }

    public static function consoleCategorySubpage(string $title, Console $console, ?Category $category, ?Category $parentCategory = null): BreadcrumbNav
    {
        return new BreadcrumbNav(array_values(array_filter([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem('By category', route('console.byCategory.landing', ['console' => $console])),
            $parentCategory ? new BreadcrumbItem($parentCategory->name, route('console.byCategory.page', ['console' => $console, 'category' => $parentCategory->link_title])) : null,
            new BreadcrumbItem($category->name, route('console.byCategory.page', ['console' => $console, 'category' => $category->link_title])),
            new BreadcrumbItem($title),
        ])));
    }

    public static function consoleSeriesSubpage(string $title, Console $console): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem('By series', route('console.bySeries.landing', ['console' => $console])),
            new BreadcrumbItem($title),
        ]);
    }

    public static function consoleCollectionSubpage(string $title, Console $console): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem('By collection', route('console.byCollection.landing', ['console' => $console])),
            new BreadcrumbItem($title),
        ]);
    }

    public static function consoleTagSubpage(string $title, Console $console): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem('By tag', route('console.byTag.landing', ['console' => $console])),
            new BreadcrumbItem($title),
        ]);
    }

    // *** TOP RATED *** //

    public static function topRated(Console $console): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem("Top Rated"),
        ]);
    }

    public static function topRatedYear(Console $console, int $year): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem("Top Rated", route('console.topRated.landing', ['console' => $console])),
            new BreadcrumbItem((string) $year),
        ]);
    }

    public static function topRatedAllTime(Console $console): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem($console->name, route('console.landing', ['console' => $console])),
            new BreadcrumbItem("Top Rated", route('console.topRated.landing', ['console' => $console])),
            new BreadcrumbItem('All-time'),
        ]);
    }

    // *** PARTNERS *** //

    public static function partnersSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem('Partners', route('partners.landing')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** HELP *** //

    public static function helpSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem('Help', route('help.landing')),
            new BreadcrumbItem($title),
        ]);
    }

    // *** NEWS *** //

    public static function newsSubpage(string $title): BreadcrumbNav
    {
        return new BreadcrumbNav([
            new BreadcrumbItem('Home', route('welcome')),
            new BreadcrumbItem('News', route('news.landing')),
            new BreadcrumbItem($title),
        ]);
    }

}