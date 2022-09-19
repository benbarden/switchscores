<?php

use Illuminate\Support\Facades\Route;

// Front page
Route::get('/', 'WelcomeController@show')->name('welcome');

Auth::routes();

// Third-party logins
Route::get('/login/twitter', 'Auth\LoginController@redirectToProviderTwitter')->name('auth.login.twitter');
Route::get('/login/twitter/callback', 'Auth\LoginController@handleProviderCallbackTwitter')->name('auth.login.twitter.callback');

// Static content
Route::get('/about', 'AboutController@landing')->name('about.landing');
Route::get('/about/changelog', 'AboutController@changelog')->name('about.changelog');
Route::get('/privacy', 'PrivacyController@show')->name('privacy');

// Help
Route::get('/help', 'HelpController@landing')->name('help.landing');
Route::get('/help/low-quality-filter', 'HelpController@lowQualityFilter')->name('help.low-quality-filter');

// Lists
Route::get('/lists', 'ListsController@landing')->name('lists.landing');
Route::get('/games/recent', 'ListsController@recentReleases')->name('games.recentReleases');
Route::get('/games/upcoming', 'ListsController@upcomingReleases')->name('games.upcomingReleases');
Route::get('/games/on-sale', 'ListsController@gamesOnSale')->name('games.onSale');
Route::get('/games/on-sale/archive', 'ListsController@gamesOnSaleArchive')->name('lists.gamesOnSaleArchive');
Route::get('/reviews', 'ListsController@recentReviews')->name('reviews.landing');
Route::get('/lists/recently-ranked', 'ListsController@recentlyRanked')->name('lists.recently-ranked');
Route::get('/lists/recently-reviewed-still-unranked', 'ListsController@recentlyReviewedStillUnranked')->name('lists.recently-reviewed-still-unranked');

// Main game pages
Route::match(['get', 'post'], '/games', 'Games\LandingController@landing')->name('games.landing');
Route::match(['get', 'post'], '/games/search', 'Games\SearchController@show')->name('games.search');

// Browse by...
Route::get('/games/by-title', 'Games\BrowseByTitleController@landing')->name('games.browse.byTitle.landing');
Route::get('/games/by-title/{letter}', 'Games\BrowseByTitleController@page')->name('games.browse.byTitle.page');

Route::get('/games/by-category', 'Games\BrowseByCategoryController@landing')->name('games.browse.byCategory.landing');
Route::get('/games/by-category/{category}', 'Games\BrowseByCategoryController@page')->name('games.browse.byCategory.page');

Route::get('/games/by-series', 'Games\BrowseBySeriesController@landing')->name('games.browse.bySeries.landing');
Route::get('/games/by-series/{series}', 'Games\BrowseBySeriesController@page')->name('games.browse.bySeries.page');

Route::get('/games/by-collection', 'Games\BrowseByCollectionController@landing')->name('games.browse.byCollection.landing');
Route::get('/games/by-collection/{collection}', 'Games\BrowseByCollectionController@page')->name('games.browse.byCollection.page');

Route::get('/games/by-tag', 'Games\BrowseByTagController@landing')->name('games.browse.byTag.landing');
Route::get('/games/by-tag/{tag}', 'Games\BrowseByTagController@page')->name('games.browse.byTag.page');

Route::get('/games/by-date', 'Games\BrowseByDateController@landing')->name('games.browse.byDate.landing');
Route::get('/games/by-date/{date}', 'Games\BrowseByDateController@page')->name('games.browse.byDate.page');

// These must be after the game redirects
Route::get('/games/{id}', 'Games\GameShowController@showId')->name('game.showId');
Route::get('/games/{id}/{linkTitle}', 'Games\GameShowController@show')->name('game.show');

/* Top Rated */
Route::get('/top-rated', 'TopRatedController@landing')->name('topRated.landing');
Route::get('/top-rated/all-time', 'TopRatedController@allTime')->name('topRated.allTime');
Route::get('/top-rated/by-year/{year}', 'TopRatedController@byYear')->name('topRated.byYear');
Route::get('/top-rated/multiplayer', 'TopRatedController@multiplayer')->name('topRated.multiplayer');

/* Reviews */
Route::get('/reviews/{year}', 'ReviewsController@landingByYear')->name('reviews.landing.byYear');

/* Partners */
Route::get('/partners', 'PartnersController@landing')->name('partners.landing');
Route::get('/partners/guides/{guideTitle}', 'PartnersController@guidesShow')->name('partners.guides.show');

Route::get('/partners/review-sites', 'ReviewSitesController@landing')->name('partners.review-sites.landing');
Route::get('/reviews/site/{linkTitle}', 'ReviewSitesController@siteProfile')->name('partners.review-sites.siteProfile');

Route::get('/partners/games-companies', 'GamesCompaniesController@landing')->name('partners.games-companies.landing');
Route::get('/partners/games-company/{linkTitle}', 'GamesCompaniesController@companyProfile')->name('partners.detail.games-company');

/* News */
Route::get('/news', 'NewsController@landing')->name('news.landing');
Route::get('/news/category/{linkName}', 'NewsController@categoryLanding')->name('news.category.landing');
Route::get('/news/{date}/{title}', 'NewsController@displayContent')->name('news.content');

// Community
Route::get('/community', 'CommunityController@landing')->name('community.landing');

// Sitemaps
Route::get('/sitemap', 'SitemapController@show')->name('sitemap.index');
Route::get('/sitemap/site', 'SitemapController@site')->name('sitemap.site');
Route::get('/sitemap/games', 'SitemapController@games')->name('sitemap.games');
Route::get('/sitemap/calendar', 'SitemapController@calendar')->name('sitemap.calendar');
Route::get('/sitemap/top-rated', 'SitemapController@topRated')->name('sitemap.topRated');
Route::get('/sitemap/reviews', 'SitemapController@reviews')->name('sitemap.reviews');
Route::get('/sitemap/tags', 'SitemapController@tags')->name('sitemap.tags');
Route::get('/sitemap/news', 'SitemapController@news')->name('sitemap.news');

