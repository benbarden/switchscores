<?php

use Illuminate\Support\Facades\Route;

use App\Models\Console;

// Front page
Route::get('/', 'PublicSite\WelcomeController@show')->name('welcome');

Auth::routes();

// Request invite code
Route::match(['get', 'post'], '/request-invite-code', 'Auth\RegisterController@requestInviteCode')->name('auth.register.request-invite-code');
Route::get('/invite-request-success', 'PublicSite\AboutController@inviteRequestSuccess')->name('about.invite-request-success');
Route::get('/invite-request-failure', 'PublicSite\AboutController@inviteRequestFailure')->name('about.invite-request-failure');

// Third-party logins
Route::get('/login/twitter', 'Auth\LoginController@redirectToProviderTwitter')->name('auth.login.twitter');
Route::get('/login/twitter/callback', 'Auth\LoginController@handleProviderCallbackTwitter')->name('auth.login.twitter.callback');

// Static content
Route::controller('PublicSite\AboutController')->group(function () {
    Route::get('/about', 'landing')->name('about.landing');
    Route::get('/about/changelog', 'changelog')->name('about.changelog');
});

Route::get('/privacy', 'PublicSite\PrivacyController@show')->name('privacy');

// Help
Route::get('/help', 'PublicSite\HelpController@landing')->name('help.landing');
Route::get('/help/low-quality-filter', 'PublicSite\HelpController@lowQualityFilter')->name('help.low-quality-filter');

// Lists
Route::redirect('/lists', '/games')->name('lists.landing');
Route::redirect('/games/recent', '/c/switch-1/new-releases')->name('games.recentReleases');
Route::redirect('/games/upcoming', '/c/switch-1/upcoming')->name('games.upcomingReleases');
Route::get('/games/on-sale', 'PublicSite\ListsController@gamesOnSale')->name('games.onSale');
Route::get('/reviews', 'PublicSite\ListsController@recentReviews')->name('reviews.landing');
Route::get('/lists/recently-ranked', 'PublicSite\ListsController@recentlyRanked')->name('lists.recently-ranked');
Route::get('/lists/buyers-guide-holiday-2024-us', 'PublicSite\ListsController@buyersGuideHoliday2024US')->name('lists.buyersGuideHoliday2024US');

// Switch 1/2
Route::controller('PublicSite\Console\ConsoleController')->group(function () {
    Route::get('/c/{console:slug?}', 'landing')->name('console.landing');
    Route::get('/c/{console:slug?}/new-releases', 'newReleases')->name('console.newReleases');
    Route::get('/c/{console:slug?}/upcoming', 'upcomingReleases')->name('console.upcomingReleases');
});
Route::controller('PublicSite\Console\BrowseByDateController')->group(function () {
    Route::get('/c/{console:slug?}/{year}', 'byYear')->name('console.byYear');
    Route::get('/c/{console:slug?}/{year}/{month}', 'byMonth')->name('console.byMonth');
});

Route::controller('PublicSite\Console\BrowseController')->group(function () {
    Route::get('/c/{console:slug?}/category/{category}', 'byCategoryLanding')->name('console.byCategoryLanding');
    Route::get('/c/{console:slug?}/category', 'byCategoryPage')->name('console.byCategoryPage');
    Route::get('/c/{console:slug?}/series/{series}', 'bySeriesLanding')->name('console.bySeriesLanding');
    Route::get('/c/{console:slug?}/series', 'bySeriesPage')->name('console.bySeriesPage');
    Route::get('/c/{console:slug?}/collection/{collection}', 'byCollectionLanding')->name('console.byCollectionLanding');
    Route::get('/c/{console:slug?}/collection', 'byCollectionPage')->name('console.byCollectionPage');
    Route::get('/c/{console:slug?}/tag/{tag}', 'byTagLanding')->name('console.byTagLanding');
    Route::get('/c/{console:slug?}/tag', 'byTagPage')->name('console.byTagPage');
});

// Main game pages
Route::redirect('/games', '/c/switch-1')->name('games.landing');
Route::match(['get', 'post'], '/games/search', 'PublicSite\Games\SearchController@show')->name('games.search');

// Browse by...
Route::get('/games/by-title', 'PublicSite\Games\BrowseByTitleController@landing')->name('games.browse.byTitle.landing');
Route::get('/games/by-title/{letter}', 'PublicSite\Games\BrowseByTitleController@page')->name('games.browse.byTitle.page');

Route::get('/games/by-category', 'PublicSite\Games\BrowseByCategoryController@landing')->name('games.browse.byCategory.landing');
Route::get('/games/by-category/{category}', 'PublicSite\Games\BrowseByCategoryController@page')->name('games.browse.byCategory.page');

Route::get('/games/by-series', 'PublicSite\Games\BrowseBySeriesController@landing')->name('games.browse.bySeries.landing');
Route::get('/games/by-series/{series}', 'PublicSite\Games\BrowseBySeriesController@page')->name('games.browse.bySeries.page');

Route::get('/games/by-collection', 'PublicSite\Games\BrowseByCollectionController@landing')->name('games.browse.byCollection.landing');
Route::get('/games/by-collection/{collection}', 'PublicSite\Games\BrowseByCollectionController@page')->name('games.browse.byCollection.page');
Route::get('/games/by-collection/{collection}/category/{category}', 'PublicSite\Games\BrowseByCollectionController@pageCategory')->name('games.browse.byCollection.pageCategory');
Route::get('/games/by-collection/{collection}/series/{series}', 'PublicSite\Games\BrowseByCollectionController@pageSeries')->name('games.browse.byCollection.pageSeries');

Route::get('/games/by-tag', 'PublicSite\Games\BrowseByTagController@landing')->name('games.browse.byTag.landing');
Route::get('/games/by-tag/{tag}', 'PublicSite\Games\BrowseByTagController@page')->name('games.browse.byTag.page');

Route::redirect('/games/by-date', '/c/switch-1/2025')->name('games.browse.byDate.landing');
Route::redirect('/games/by-date/{date}', '/c/switch-1/2025')->name('games.browse.byDate.page');

// Random
Route::get('/games/random', 'PublicSite\Games\RandomController@getRandom')->name('game.random');

// These must be after the game redirects
Route::get('/games/{id}', 'PublicSite\Games\GameShowController@showId')->name('game.showId');
Route::get('/games/{id}/{linkTitle}', 'PublicSite\Games\GameShowController@show')->name('game.show');

/* Top Rated */
Route::get('/top-rated', 'PublicSite\TopRatedController@landing')->name('topRated.landing');
Route::get('/top-rated/all-time', 'PublicSite\TopRatedController@allTime')->name('topRated.allTime');
Route::get('/top-rated/all-time/page/{page}', 'PublicSite\TopRatedController@allTimePage')->name('topRated.allTime.page');
Route::get('/top-rated/by-year/{year}', 'PublicSite\TopRatedController@byYear')->name('topRated.byYear');

/* Reviews */
Route::redirect('/reviews/{year}', '/c/switch-1/{year}')->name('reviews.landing.byYear');

/* Partners */
Route::get('/partners', 'PublicSite\PartnersController@landing')->name('partners.landing');
Route::get('/partners/guides/{guideTitle}', 'PublicSite\PartnersController@guidesShow')->name('partners.guides.show');

Route::get('/partners/review-sites', 'PublicSite\ReviewSitesController@landing')->name('partners.review-sites.landing');
Route::get('/reviews/site/{linkTitle}', 'PublicSite\ReviewSitesController@siteProfile')->name('partners.review-sites.siteProfile');

Route::get('/partners/games-companies', 'PublicSite\GamesCompaniesController@landing')->name('partners.games-companies.landing');
Route::match(['get', 'post'], '/partners/games-companies/signup', 'PublicSite\GamesCompaniesController@signupPage')->name('partners.games-companies.signupPage');
Route::match(['get'], '/partners/games-companies/signup/success', 'PublicSite\GamesCompaniesController@signupSuccess')->name('partners.games-companies.signupSuccess');
Route::get('/partners/games-company/{linkTitle}', 'PublicSite\GamesCompaniesController@companyProfile')->name('partners.detail.games-company');

/* News */
Route::get('/news', 'PublicSite\NewsController@landing')->name('news.landing');
Route::get('/news/database-updates/{year}/{week}', 'PublicSite\NewsController@databaseUpdates')->name('news.databaseUpdates');
Route::get('/news/archive', 'PublicSite\NewsController@landingArchive')->name('news.archive');
Route::get('/news/category/{linkName}', 'PublicSite\NewsController@categoryLanding')->name('news.category.landing');
Route::get('/news/{date}/{title}', 'PublicSite\NewsController@displayContent')->name('news.content');

// Community
Route::get('/community', 'PublicSite\CommunityController@landing')->name('community.landing');

// Sitemaps
Route::controller('SitemapController')->group(function () {
    Route::redirect('/sitemap', '/sitemaps/index.xml');
    Route::redirect('/sitemap/site', '/sitemaps/sitemap-site.xml');
    Route::redirect('/sitemap/games', '/sitemaps/sitemap-games.xml');
    Route::redirect('/sitemap/calendar', '/sitemaps/sitemap-calendar.xml');
    Route::redirect('/sitemap/top-rated', '/sitemaps/sitemap-top-rated.xml');
    Route::redirect('/sitemap/reviews', '/sitemaps/sitemap-reviews.xml');
    Route::redirect('/sitemap/tags', '/sitemaps/sitemap-tags.xml');
//    Route::redirect('/sitemap/news', '/sitemaps/sitemap-news.xml');
});
