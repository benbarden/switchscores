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
Route::redirect('/lists', '/c/switch-1', 301)->name('lists.landing');
Route::redirect('/games/recent', '/c/switch-1/new-releases', 301)->name('games.recentReleases');
Route::redirect('/games/upcoming', '/c/switch-1/upcoming', 301)->name('games.upcomingReleases');
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
Route::controller('PublicSite\Console\TopRatedController')->group(function () {
    Route::get('/c/{console:slug?}/top-rated', 'landing')->name('console.topRated.landing');
    Route::get('/c/{console:slug?}/top-rated/all-time', 'allTime')->name('console.topRated.allTime');
    Route::get('/c/{console:slug?}/top-rated/all-time/page/{page}', 'allTimePage')->name('console.topRated.allTime.page');
    Route::get('/c/{console:slug?}/top-rated/by-year/{year}', 'byYear')->name('console.topRated.byYear');
});
Route::controller('PublicSite\Console\BrowseByCategoryController')->group(function () {
    Route::get('/c/{console:slug?}/category', 'landing')->name('console.byCategory.landing');
    Route::get('/c/{console:slug?}/category/{category}', 'page')->name('console.byCategory.page');
});
Route::controller('PublicSite\Console\BrowseBySeriesController')->group(function () {
    Route::get('/c/{console:slug?}/series', 'landing')->name('console.bySeries.landing');
    Route::get('/c/{console:slug?}/series/{series}', 'page')->name('console.bySeries.page');
});
Route::controller('PublicSite\Console\BrowseByCollectionController')->group(function () {
    Route::get('/c/{console:slug?}/collection', 'landing')->name('console.byCollection.landing');
    Route::get('/c/{console:slug?}/collection/{collection}', 'page')->name('console.byCollection.page');
});
Route::controller('PublicSite\Console\BrowseByTagController')->group(function () {
    Route::get('/c/{console:slug?}/tag', 'landing')->name('console.byTag.landing');
    Route::get('/c/{console:slug?}/tag/{tag}', 'page')->name('console.byTag.page');
});
// These must appear after the other console links
Route::controller('PublicSite\Console\BrowseByDateController')->group(function () {
    Route::get('/c/{console:slug?}/{year}', 'byYear')->name('console.byYear');
    Route::get('/c/{console:slug?}/{year}/{month}', 'byMonth')->name('console.byMonth');
});

// Main game pages
Route::redirect('/games', '/c/switch-1', 301)->name('games.landing');
Route::match(['get', 'post'], '/games/search', 'PublicSite\Games\SearchController@show')->name('games.search');

// Browse by...
Route::redirect('/games/by-title', '/c/switch-1', 301)->name('games.browse.byTitle.landing');
Route::redirect('/games/by-title/{letter}', '/c/switch-1', 301)->name('games.browse.byTitle.page');

Route::redirect('/games/by-category', '/c/switch-1/category', 301)->name('games.browse.byCategory.landing');
Route::redirect('/games/by-category/{category}', '/c/switch-1/category/{category}', 301)->name('games.browse.byCategory.page');

Route::redirect('/games/by-series', '/c/switch-1/series', 301)->name('games.browse.bySeries.landing');
Route::redirect('/games/by-series/{series}', '/c/switch-1/series/{series}', 301)->name('games.browse.bySeries.page');

Route::redirect('/games/by-collection', '/c/switch-1/collection', 301)->name('games.browse.byCollection.landing');
Route::redirect('/games/by-collection/{collection}', '/c/switch-1/collection/{collection}', 301)->name('games.browse.byCollection.page');
//Route::get('/games/by-collection/{collection}/category/{category}', 'PublicSite\Games\BrowseByCollectionController@pageCategory')->name('games.browse.byCollection.pageCategory');
//Route::get('/games/by-collection/{collection}/series/{series}', 'PublicSite\Games\BrowseByCollectionController@pageSeries')->name('games.browse.byCollection.pageSeries');

Route::redirect('/games/by-tag', '/c/switch-1/tag', 301)->name('games.browse.byTag.landing');
Route::redirect('/games/by-tag/{tag}', '/c/switch-1/tag/{tag}', 301)->name('games.browse.byTag.page');

Route::redirect('/games/by-date', '/c/switch-1/2025', 301)->name('games.browse.byDate.landing');
Route::redirect('/games/by-date/{date}', '/c/switch-1/2025', 301)->name('games.browse.byDate.page');

// Random
Route::get('/games/random', 'PublicSite\Games\RandomController@getRandom')->name('game.random');

// These must be after the game redirects
Route::get('/games/{id}', 'PublicSite\Games\GameShowController@showId')->name('game.showId');
Route::get('/games/{id}/{linkTitle}', 'PublicSite\Games\GameShowController@show')->name('game.show');

/* Top Rated */
Route::redirect('/top-rated', '/c/switch-1/top-rated', 301)->name('topRated.landing');
Route::redirect('/top-rated/all-time', '/c/switch-1/top-rated/all-time', 301)->name('topRated.allTime');
Route::redirect('/top-rated/all-time/page/{page}', '/c/switch-1/top-rated/all-time/page/{page}', 301)->name('topRated.allTime.page');
Route::redirect('/top-rated/by-year/{year}', '/c/switch-1/top-rated/by-year/{year}', 301)->name('topRated.byYear');

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
    Route::redirect('/sitemap', '/sitemaps/index.xml', 301);
    Route::redirect('/sitemap/site', '/sitemaps/sitemap-site.xml', 301);
    Route::redirect('/sitemap/games', '/sitemaps/sitemap-games.xml', 301);
    Route::redirect('/sitemap/calendar', '/sitemaps/sitemap-calendar.xml', 301);
    Route::redirect('/sitemap/top-rated', '/sitemaps/sitemap-top-rated.xml', 301);
    Route::redirect('/sitemap/reviews', '/sitemaps/sitemap-reviews.xml', 301);
    Route::redirect('/sitemap/tags', '/sitemaps/sitemap-tags.xml', 301);
//    Route::redirect('/sitemap/news', '/sitemaps/sitemap-news.xml', 301);
});
