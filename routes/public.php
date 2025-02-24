<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/about', 'PublicSite\AboutController@landing')->name('about.landing');
Route::get('/about/changelog', 'PublicSite\AboutController@changelog')->name('about.changelog');
Route::get('/privacy', 'PublicSite\PrivacyController@show')->name('privacy');

// Help
Route::get('/help', 'PublicSite\HelpController@landing')->name('help.landing');
Route::get('/help/low-quality-filter', 'PublicSite\HelpController@lowQualityFilter')->name('help.low-quality-filter');

// Lists
Route::get('/lists', 'PublicSite\ListsController@landing')->name('lists.landing');
Route::get('/games/recent', 'PublicSite\ListsController@recentReleases')->name('games.recentReleases');
Route::get('/games/upcoming', 'PublicSite\ListsController@upcomingReleases')->name('games.upcomingReleases');
Route::get('/games/on-sale', 'PublicSite\ListsController@gamesOnSale')->name('games.onSale');
Route::get('/reviews', 'PublicSite\ListsController@recentReviews')->name('reviews.landing');
Route::get('/lists/recently-ranked', 'PublicSite\ListsController@recentlyRanked')->name('lists.recently-ranked');
Route::get('/lists/recently-reviewed-still-unranked', 'PublicSite\ListsController@recentlyReviewedStillUnranked')->name('lists.recently-reviewed-still-unranked');
Route::get('/lists/buyers-guide-holiday-2024-us', 'PublicSite\ListsController@buyersGuideHoliday2024US')->name('lists.buyersGuideHoliday2024US');

// Main game pages
Route::match(['get', 'post'], '/games', 'PublicSite\Games\LandingController@landing')->name('games.landing');
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

Route::get('/games/by-date', 'PublicSite\Games\BrowseByDateController@landing')->name('games.browse.byDate.landing');
Route::get('/games/by-date/{date}', 'PublicSite\Games\BrowseByDateController@page')->name('games.browse.byDate.page');

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
Route::get('/reviews/{year}', 'PublicSite\ReviewsController@landingByYear')->name('reviews.landing.byYear');

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
Route::get('/sitemap', 'SitemapController@show')->name('sitemap.index');
Route::get('/sitemap/site', 'SitemapController@site')->name('sitemap.site');
Route::get('/sitemap/games', 'SitemapController@games')->name('sitemap.games');
Route::get('/sitemap/calendar', 'SitemapController@calendar')->name('sitemap.calendar');
Route::get('/sitemap/top-rated', 'SitemapController@topRated')->name('sitemap.topRated');
Route::get('/sitemap/reviews', 'SitemapController@reviews')->name('sitemap.reviews');
Route::get('/sitemap/tags', 'SitemapController@tags')->name('sitemap.tags');
Route::get('/sitemap/news', 'SitemapController@news')->name('sitemap.news');

