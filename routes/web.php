<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Front page
Route::get('/', 'WelcomeController@show')->name('welcome');

// Static content
Route::get('/about', 'AboutController@show')->name('about');
Route::get('/privacy', 'PrivacyController@show')->name('privacy');

// Sitemaps
Route::get('/sitemap', 'SitemapController@show')->name('sitemap.index');
Route::get('/sitemap/site', 'SitemapController@site')->name('sitemap.site');
Route::get('/sitemap/charts', 'SitemapController@charts')->name('sitemap.charts');
Route::get('/sitemap/genres', 'SitemapController@genres')->name('sitemap.genres');
Route::get('/sitemap/games', 'SitemapController@games')->name('sitemap.games');
Route::get('/sitemap/news', 'SitemapController@news')->name('sitemap.news');

// Main game pages
Route::get('/games', 'GamesController@landing')->name('games.landing');
Route::get('/games/released', 'GamesController@listReleased')->name('games.list.released');
Route::get('/games/upcoming', 'GamesController@listUpcoming')->name('games.list.upcoming');

Route::get('/games/calendar', 'GamesController@calendarLanding')->name('games.calendar.landing');
Route::get('/games/calendar/{date}', 'GamesController@calendarPage')->name('games.calendar.page');

Route::get('/games/genres', 'GamesController@genresLanding')->name('games.genres.landing');
Route::get('/games/genres/{linkTitle}', 'GamesController@genreByName')->name('games.genres.item');

// Old pages - redirects
Route::get('/games/top-rated', 'GamesController@listTopRated')->name('games.list.topRated');
Route::get('/games/reviews-needed', 'GamesController@listReviewsNeeded')->name('games.list.reviewsNeeded');

// These must be after the game redirects
Route::get('/games/{id}', 'GamesController@showId')->name('game.showId');
Route::get('/games/{id}/{title}', 'GamesController@show')->name('game.show');

/* Charts */
Route::get('/charts', 'ChartsController@landing')->name('charts.landing');
Route::get('/charts/most-appearances', 'ChartsController@mostAppearances')->name('charts.mostAppearances');
Route::get('/charts/games-at-position', 'ChartsController@gamesAtPositionLanding')->name('charts.gamesAtPositionLanding');
Route::get('/charts/games-at-position/{position?}', 'ChartsController@gamesAtPosition')->name('charts.gamesAtPosition');

Route::get('/charts/{countryCode}/{date}', 'ChartsController@show')->name('charts.date.show');

/* Charts redirects (old URLs) */
Route::get('/charts/{date?}', 'ChartsController@redirectEu')->name('charts.date.redirect');
Route::get('/charts-us/{date?}', 'ChartsController@redirectUs')->name('charts.us.date.redirect');

/* Reviews */
Route::get('/reviews', 'ReviewsController@landing')->name('reviews.landing');
Route::get('/reviews/top-rated', 'ReviewsController@topRatedLanding')->name('reviews.topRated.landing');
Route::get('/reviews/top-rated/all-time', 'ReviewsController@topRatedAllTime')->name('reviews.topRated.allTime');
Route::get('/reviews/top-rated/by-year/{year}', 'ReviewsController@topRatedByYear')->name('reviews.topRated.byYear');
Route::get('/reviews/games-needing-reviews', 'ReviewsController@gamesNeedingReviews')->name('reviews.gamesNeedingReviews');

Route::get('/reviews/site/{linkTitle}', 'ReviewsController@reviewSite')->name('reviews.site');

/* News */
Route::get('/news', 'NewsController@landing')->name('news.landing');
Route::get('/news/{date}/{title}', 'NewsController@displayContent')->name('news.content');

/* Logged in */
Route::group(['middleware' => ['auth']], function() {

    // Index
    Route::get('/user', 'User\IndexController@show')->name('user.index');

    // User lists
    Route::get('/user-list-item/add', 'User\UserListController@addPlaylistItem')->name('user.list-item.add');
    Route::get('/user-list-item/delete', 'User\UserListController@deletePlaylistItem')->name('user.list-item.delete');

    // User profile
    Route::get('/user/region/update', 'User\UserProfileController@updateRegion')->name('user.profile.updateRegion');

});

/* Admin */
Route::group(['middleware' => ['auth.admin:admin']], function() {

    // Index
    Route::get('/admin', 'Admin\IndexController@show')->name('admin.index');

    // Games
    Route::get('/admin/games/list/{report?}', 'Admin\GamesController@showList')->name('admin.games.list');
    Route::get('/admin/games/add', 'Admin\GamesController@add')->name('admin.games.add');
    Route::post('/admin/games/add', 'Admin\GamesController@add')->name('admin.games.add');
    Route::get('/admin/games/edit/{gameId}', 'Admin\GamesController@edit')->name('admin.games.edit');
    Route::post('/admin/games/edit/{gameId}', 'Admin\GamesController@edit')->name('admin.games.edit');
    Route::get('/admin/games/delete/{gameId}', 'Admin\GamesController@delete')->name('admin.games.delete');
    Route::post('/admin/games/delete/{gameId}', 'Admin\GamesController@delete')->name('admin.games.delete');

    // Charts: Dates
    Route::get('/admin/charts/date', 'Admin\ChartsDateController@showList')->name('admin.charts.date.list');
    Route::get('/admin/charts/date/add', 'Admin\ChartsDateController@add')->name('admin.charts.date.add');
    Route::post('/admin/charts/date/add', 'Admin\ChartsDateController@add')->name('admin.charts.date.add');

    // Charts: Rankings
    Route::get('/admin/charts/ranking/{country}/{date}', 'Admin\ChartsRankingController@showList')->name('admin.charts.ranking.list');
    Route::get('/admin/charts/ranking/{country}/{date}/add', 'Admin\ChartsRankingController@add')->name('admin.charts.ranking.add');
    Route::post('/admin/charts/ranking/{country}/{date}/add', 'Admin\ChartsRankingController@add')->name('admin.charts.ranking.add');

    // Reviews: Sites
    Route::get('/admin/reviews/site', 'Admin\ReviewSiteController@showList')->name('admin.reviews.site.list');
    Route::get('/admin/reviews/site/add', 'Admin\ReviewSiteController@add')->name('admin.reviews.site.add');
    Route::post('/admin/reviews/site/add', 'Admin\ReviewSiteController@add')->name('admin.reviews.site.add');
    Route::get('/admin/reviews/site/edit/{siteId}', 'Admin\ReviewSiteController@edit')->name('admin.reviews.site.edit');
    Route::post('/admin/reviews/site/edit/{siteId}', 'Admin\ReviewSiteController@edit')->name('admin.reviews.site.edit');

    // Reviews: Links
    Route::get('/admin/reviews/link/add', 'Admin\ReviewLinkController@add')->name('admin.reviews.link.add');
    Route::post('/admin/reviews/link/add', 'Admin\ReviewLinkController@add')->name('admin.reviews.link.add');
    Route::get('/admin/reviews/link/edit/{linkId}', 'Admin\ReviewLinkController@edit')->name('admin.reviews.link.edit');
    Route::post('/admin/reviews/link/edit/{linkId}', 'Admin\ReviewLinkController@edit')->name('admin.reviews.link.edit');
    Route::get('/admin/reviews/link/{report?}', 'Admin\ReviewLinkController@showList')->name('admin.reviews.link.list');

    // Feed items
    Route::get('/admin/feed-items', 'Admin\IndexController@feedItemsLanding')->name('admin.feed-items.landing');
    Route::get('/admin/feed-items/reviews/{report?}', 'Admin\FeedItemReviewController@showList')->name('admin.feed-items.reviews.list');
    Route::get('/admin/feed-items/reviews/edit/{linkId}', 'Admin\FeedItemReviewController@edit')->name('admin.feed-items.reviews.edit');
    Route::post('/admin/feed-items/reviews/edit/{linkId}', 'Admin\FeedItemReviewController@edit')->name('admin.feed-items.reviews.edit');
    Route::get('/admin/feed-items/games/{report?}', 'Admin\FeedItemGameController@showList')->name('admin.feed-items.games.list');
    Route::get('/admin/feed-items/games/edit/{linkId}', 'Admin\FeedItemGameController@edit')->name('admin.feed-items.games.edit');
    Route::post('/admin/feed-items/games/edit/{linkId}', 'Admin\FeedItemGameController@edit')->name('admin.feed-items.games.edit');

    // News
    Route::get('/admin/news/list', 'Admin\NewsController@showList')->name('admin.news.list');
    Route::get('/admin/news/add', 'Admin\NewsController@add')->name('admin.news.add');
    Route::post('/admin/news/add', 'Admin\NewsController@add')->name('admin.news.add');
    Route::get('/admin/news/edit/{newsId}', 'Admin\NewsController@edit')->name('admin.news.edit');
    Route::post('/admin/news/edit/{newsId}', 'Admin\NewsController@edit')->name('admin.news.edit');

    // Tools
    Route::get('/admin/tools', 'Admin\ToolsController@landing')->name('admin.tools.landing');
    Route::get('/admin/tools/run-feed-importer/landing', 'Admin\ToolsController@runFeedImporterLanding')->name('admin.tools.runFeedImporter.landing');
    Route::get('/admin/tools/run-feed-importer/process', 'Admin\ToolsController@runFeedImporterProcess')->name('admin.tools.runFeedImporter.process');
    Route::get('/admin/tools/run-feed-parser/landing', 'Admin\ToolsController@runFeedParserLanding')->name('admin.tools.runFeedParser.landing');
    Route::get('/admin/tools/run-feed-parser/process', 'Admin\ToolsController@runFeedParserProcess')->name('admin.tools.runFeedParser.process');
    Route::get('/admin/tools/run-feed-review-generator/landing', 'Admin\ToolsController@runFeedReviewGeneratorLanding')->name('admin.tools.runFeedReviewGenerator.landing');
    Route::get('/admin/tools/run-feed-review-generator/process', 'Admin\ToolsController@runFeedReviewGeneratorProcess')->name('admin.tools.runFeedReviewGenerator.process');
    Route::get('/admin/tools/run-wikipedia-crawler/landing', 'Admin\ToolsController@runWikipediaCrawlerLanding')->name('admin.tools.runWikipediaCrawler.landing');
    Route::get('/admin/tools/run-wikipedia-crawler/process', 'Admin\ToolsController@runWikipediaCrawlerProcess')->name('admin.tools.runWikipediaCrawler.process');
    Route::get('/admin/tools/run-wikipedia-importer/landing', 'Admin\ToolsController@runWikipediaImporterLanding')->name('admin.tools.runWikipediaImporter.landing');
    Route::get('/admin/tools/run-wikipedia-importer/process', 'Admin\ToolsController@runWikipediaImporterProcess')->name('admin.tools.runWikipediaImporter.process');
    Route::get('/admin/tools/run-wikipedia-updater/landing', 'Admin\ToolsController@runWikipediaUpdaterLanding')->name('admin.tools.runWikipediaUpdater.landing');
    Route::get('/admin/tools/run-wikipedia-updater/process', 'Admin\ToolsController@runWikipediaUpdaterProcess')->name('admin.tools.runWikipediaUpdater.process');
    Route::get('/admin/tools/update-game-ranks/landing', 'Admin\ToolsController@updateGameRanksLanding')->name('admin.tools.updateGameRanks.landing');
    Route::get('/admin/tools/update-game-ranks/process', 'Admin\ToolsController@updateGameRanksProcess')->name('admin.tools.updateGameRanks.process');
    Route::get('/admin/tools/update-game-image-count/landing', 'Admin\ToolsController@updateGameImageCountLanding')->name('admin.tools.updateGameImageCount.landing');
    Route::get('/admin/tools/update-game-image-count/process', 'Admin\ToolsController@updateGameImageCountProcess')->name('admin.tools.updateGameImageCount.process');
    Route::get('/admin/tools/update-game-review-stats/landing', 'Admin\ToolsController@updateGameReviewStatsLanding')->name('admin.tools.updateGameReviewStats.landing');
    Route::get('/admin/tools/update-game-review-stats/process', 'Admin\ToolsController@updateGameReviewStatsProcess')->name('admin.tools.updateGameReviewStats.process');

    // News
    Route::get('/admin/user/list', 'Admin\UserController@showList')->name('admin.user.list');

});

Auth::routes();



/* Misc redirects */
Route::get('/lists/released-nintendo-switch-games', 'ListsController@releasedGames')->name('lists.released');
Route::get('/lists/upcoming-nintendo-switch-games', 'ListsController@upcomingGames')->name('lists.upcoming');

// **** NOTE: THESE NEED TO BE LAST! **** //

/* Blog redirects */
//Route::get('/tag/{tag}/', 'BlogController@redirectTag')->name('blog.redirectTag');
//Route::get('/category/{tag}/', 'BlogController@redirectCategory')->name('blog.redirectCategory');
//Route::get('/{year}/{month}/{day}/{title}/', 'BlogController@redirectPost')->name('blog.redirectPost');
