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

// Main game pages
Route::get('/games', 'GamesController@landing')->name('games.landing');
Route::get('/games/recent', 'GamesController@recentReleases')->name('games.recentReleases');
Route::get('/games/upcoming', 'GamesController@upcomingReleases')->name('games.upcomingReleases');
//Route::get('/games/upcoming', 'GamesController@listUpcoming')->name('games.list.upcoming');

Route::get('/games/on-sale', 'GamesController@gamesOnSale')->name('games.onSale');

// Browse by...
Route::get('/games/by-title', 'GamesBrowseController@byTitleLanding')->name('games.browse.byTitle.landing');
Route::get('/games/by-title/{letter}', 'GamesBrowseController@byTitlePage')->name('games.browse.byTitle.page');

Route::get('/games/by-type', 'GamesBrowseController@byPrimaryTypeLanding')->name('games.browse.byPrimaryType.landing');
Route::get('/games/by-type/{primaryType}', 'GamesBrowseController@byPrimaryTypePage')->name('games.browse.byPrimaryType.page');

Route::get('/games/by-series', 'GamesBrowseController@bySeriesLanding')->name('games.browse.bySeries.landing');
Route::get('/games/by-series/{series}', 'GamesBrowseController@bySeriesPage')->name('games.browse.bySeries.page');

Route::get('/games/by-tag', 'GamesBrowseController@byTagLanding')->name('games.browse.byTag.landing');
Route::get('/games/by-tag/{tag}', 'GamesBrowseController@byTagPage')->name('games.browse.byTag.page');

Route::get('/games/by-date', 'GamesBrowseController@byDateLanding')->name('games.browse.byDate.landing');
Route::get('/games/by-date/{date}', 'GamesBrowseController@byDatePage')->name('games.browse.byDate.page');

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

/* Top Rated */
Route::get('/top-rated', 'TopRatedController@landing')->name('topRated.landing');
Route::get('/top-rated/all-time', 'TopRatedController@allTime')->name('topRated.allTime');
Route::get('/top-rated/by-year/{year}', 'TopRatedController@byYear')->name('topRated.byYear');
Route::get('/top-rated/by-month', 'TopRatedController@byMonthLanding')->name('topRated.byMonthLanding');
Route::get('/top-rated/by-month/{date}', 'TopRatedController@byMonthPage')->name('topRated.byMonthPage');

/* Reviews */
Route::get('/reviews', 'ReviewsController@landing')->name('reviews.landing');
Route::get('/reviews/site/{linkTitle}', 'ReviewsController@reviewSite')->name('reviews.site');
Route::get('/reviews/games-needing-reviews', 'ReviewsController@gamesNeedingReviews')->name('reviews.gamesNeedingReviews');

/* Partners */
Route::get('/partners', 'PartnersController@landing')->name('partners.landing');
Route::get('/partners/review-sites', 'PartnersController@reviewSites')->name('partners.review-sites');
Route::get('/partners/developers-publishers', 'PartnersController@developersPublishers')->name('partners.developers-publishers');

Route::get('/partners/games-company/{linkTitle}', 'PartnersController@showGamesCompany')->name('partners.detail.games-company');

/* News */
Route::get('/news', 'NewsController@landing')->name('news.landing');
Route::get('/news/{date}/{title}', 'NewsController@displayContent')->name('news.content');

// Sitemaps
Route::get('/sitemap', 'SitemapController@show')->name('sitemap.index');
Route::get('/sitemap/site', 'SitemapController@site')->name('sitemap.site');
Route::get('/sitemap/games', 'SitemapController@games')->name('sitemap.games');
Route::get('/sitemap/calendar', 'SitemapController@calendar')->name('sitemap.calendar');
Route::get('/sitemap/top-rated', 'SitemapController@topRated')->name('sitemap.topRated');
Route::get('/sitemap/reviews', 'SitemapController@reviews')->name('sitemap.reviews');
Route::get('/sitemap/tags', 'SitemapController@tags')->name('sitemap.tags');
Route::get('/sitemap/news', 'SitemapController@news')->name('sitemap.news');

/* Logged in */
Route::group(['middleware' => ['auth']], function() {

    // Index
    Route::get('/user', 'User\IndexController@show')->name('user.index');

    // Settings
    Route::get('/user/settings', 'User\SettingsController@show')->name('user.settings');

    // Collection
    Route::get('/user/collection/index', 'User\CollectionController@landing')->name('user.collection.landing');
    Route::get('/user/collection/add', 'User\CollectionController@add')->name('user.collection.add');
    Route::post('/user/collection/add', 'User\CollectionController@add')->name('user.collection.add');
    Route::get('/user/collection/edit/{itemId}', 'User\CollectionController@edit')->name('user.collection.edit');
    Route::post('/user/collection/edit/{itemId}', 'User\CollectionController@edit')->name('user.collection.edit');
    Route::get('/user/collection/delete', 'User\CollectionController@delete')->name('user.collection.delete');

    // User profile
    Route::get('/user/region/update', 'User\UserProfileController@updateRegion')->name('user.profile.updateRegion');

    // User lists
    Route::get('/user-list-item/add', 'User\UserListController@addPlaylistItem')->name('user.list-item.add');
    Route::get('/user-list-item/delete', 'User\UserListController@deletePlaylistItem')->name('user.list-item.delete');

    // User reviews
    Route::get('/user/reviews/add', 'User\ReviewUserController@add')->name('user.reviews.add');
    Route::post('/user/reviews/add', 'User\ReviewUserController@add')->name('user.reviews.add');
    Route::get('/user/reviews/{report?}', 'User\ReviewUserController@showList')->name('user.reviews.list');

    // Partner reviews
    Route::get('/user/partner-reviews/add', 'User\PartnerReviewController@add')->name('user.partner-reviews.add');
    Route::post('/user/partner-reviews/add', 'User\PartnerReviewController@add')->name('user.partner-reviews.add');
    Route::get('/user/partner-reviews/{report?}', 'User\PartnerReviewController@showList')->name('user.partner-reviews.list');

    // Review partners: Unranked games
    Route::get('/user/review-partner/unranked', 'User\ReviewPartnerUnrankedController@landing')->name('user.review-partner.unranked.landing');
    Route::get('/user/review-partner/unranked/{mode}/{filter}', 'User\ReviewPartnerUnrankedController@showList')->name('user.review-partner.unranked.list');

    // Review partners: Review links
    Route::get('/user/review-link/{report?}', 'User\ReviewLinkController@landing')->name('user.review-link.landing');

    // Review partners: Games list
    Route::get('/user/games-list/{report}', 'User\GamesListController@landing')->name('user.games-list.landing');

});

/* Admin */
Route::group(['middleware' => ['auth.admin:admin']], function() {

    // Index
    Route::get('/admin', 'Admin\IndexController@show')->name('admin.index');

    // Games: Core
    Route::get('/admin/games/list/{report?}', 'Admin\GamesController@showList')->name('admin.games.list');

    // Games: Filter list
    Route::get('/admin/games/filter-list/with-tag/{linkTitle}', 'Admin\GamesFilterListController@gamesWithTag')->name('admin.games-filter.games-with-tag');

    // Games: Detail
    Route::get('/admin/games/detail/{gameId}', 'Admin\GamesDetailController@show')->name('admin.games.detail');

    // Games: Add, edit, delete
    Route::get('/admin/games/add', 'Admin\GamesController@add')->name('admin.games.add');
    Route::post('/admin/games/add', 'Admin\GamesController@add')->name('admin.games.add');
    Route::get('/admin/games/edit/{gameId}', 'Admin\GamesController@edit')->name('admin.games.edit');
    Route::post('/admin/games/edit/{gameId}', 'Admin\GamesController@edit')->name('admin.games.edit');
    Route::get('/admin/games/delete/{gameId}', 'Admin\GamesController@delete')->name('admin.games.delete');
    Route::post('/admin/games/delete/{gameId}', 'Admin\GamesController@delete')->name('admin.games.delete');
    Route::get('/admin/games/release', 'Admin\GamesController@releaseGame')->name('admin.games.release');
    Route::get('/admin/games/update-eshop-data', 'Admin\GamesController@updateEshopData')->name('admin.games.updateEshopData');
    Route::get('/admin/games/redownload-packshots', 'Admin\GamesController@redownloadPackshots')->name('admin.games.redownloadPackshots');

    // Game change history
    Route::get('/admin/game-change-history/{filter?}', 'Admin\GameChangeHistoryController@index')->name('admin.game-change-history.index');

    // Games: Title hashes
    Route::get('/admin/games-title-hash/list/{gameId?}', 'Admin\GamesTitleHashController@showList')->name('admin.games-title-hash.list');
    Route::get('/admin/games-title-hash/add', 'Admin\GamesTitleHashController@add')->name('admin.games-title-hash.add');
    Route::post('/admin/games-title-hash/add', 'Admin\GamesTitleHashController@add')->name('admin.games-title-hash.add');
    Route::get('/admin/games-title-hash/edit/{itemId}', 'Admin\GamesTitleHashController@edit')->name('admin.games-title-hash.edit');
    Route::post('/admin/games-title-hash/edit/{itemId}', 'Admin\GamesTitleHashController@edit')->name('admin.games-title-hash.edit');
    Route::get('/admin/games-title-hash/delete/{itemId}', 'Admin\GamesTitleHashController@delete')->name('admin.games-title-hash.delete');
    Route::post('/admin/games-title-hash/delete/{itemId}', 'Admin\GamesTitleHashController@delete')->name('admin.games-title-hash.delete');

    // Games partner links
    Route::get('/admin/game/partner/{gameId}/list', 'Admin\GamePartnerController@showGamePartners')->name('admin.game.partner.list');
    Route::get('/admin/game/partner/{gameId}/save-dev-pub', 'Admin\GamePartnerController@saveDevPub')->name('admin.game.partner.saveDevPub');
    Route::get('/admin/game/partner/{gameId}/legacy-fix-dev', 'Admin\GamePartnerController@legacyFixDev')->name('admin.game.partner.legacyFixDev');
    Route::get('/admin/game/partner/{gameId}/legacy-fix-pub', 'Admin\GamePartnerController@legacyFixPub')->name('admin.game.partner.legacyFixPub');
    Route::get('/admin/game/partner/create-new-company', 'Admin\GamePartnerController@createNewCompany')->name('admin.game.partner.createNewCompany');
    Route::get('/admin/game/developer/{gameId}/add', 'Admin\GamePartnerController@addGameDeveloper')->name('admin.game.developer.add');
    Route::get('/admin/game/developer/{gameId}/remove', 'Admin\GamePartnerController@removeGameDeveloper')->name('admin.game.developer.remove');
    Route::get('/admin/game/publisher/{gameId}/add', 'Admin\GamePartnerController@addGamePublisher')->name('admin.game.publisher.add');
    Route::get('/admin/game/publisher/{gameId}/remove', 'Admin\GamePartnerController@removeGamePublisher')->name('admin.game.publisher.remove');

    // Action lists
    Route::get('/admin/action-lists', 'Admin\ActionListController@landing')->name('admin.action-lists.landing');
    Route::get('/admin/action-lists/developer-missing', 'Admin\ActionListController@developerMissing')->name('admin.action-lists.developer-missing');
    Route::get('/admin/action-lists/new-developer-to-set', 'Admin\ActionListController@newDeveloperToSet')->name('admin.action-lists.new-developer-to-set');
    Route::get('/admin/action-lists/old-developer-to-clear', 'Admin\ActionListController@oldDeveloperToClear')->name('admin.action-lists.old-developer-to-clear');
    Route::get('/admin/action-lists/publisher-missing', 'Admin\ActionListController@publisherMissing')->name('admin.action-lists.publisher-missing');
    Route::get('/admin/action-lists/new-publisher-to-set', 'Admin\ActionListController@newPublisherToSet')->name('admin.action-lists.new-publisher-to-set');
    Route::get('/admin/action-lists/old-publisher-to-clear', 'Admin\ActionListController@oldPublisherToClear')->name('admin.action-lists.old-publisher-to-clear');
    Route::get('/admin/action-lists/no-price', 'Admin\ActionListController@noPrice')->name('admin.action-lists.no-price');
    Route::get('/admin/action-lists/site-alert-errors', 'Admin\ActionListController@siteAlertErrors')->name('admin.action-lists.site-alert-errors');

    // Charts: Dates
    Route::get('/admin/charts/date', 'Admin\ChartsDateController@showList')->name('admin.charts.date.list');
    Route::get('/admin/charts/date/add', 'Admin\ChartsDateController@add')->name('admin.charts.date.add');
    Route::post('/admin/charts/date/add', 'Admin\ChartsDateController@add')->name('admin.charts.date.add');

    // Charts: Rankings
    Route::get('/admin/charts/ranking/{country}/{date}', 'Admin\ChartsRankingController@showList')->name('admin.charts.ranking.list');
    Route::get('/admin/charts/ranking/{country}/{date}/add', 'Admin\ChartsRankingController@add')->name('admin.charts.ranking.add');
    Route::post('/admin/charts/ranking/{country}/{date}/add', 'Admin\ChartsRankingController@add')->name('admin.charts.ranking.add');

    // Reviews: Links
    Route::get('/admin/reviews/link/add', 'Admin\ReviewLinkController@add')->name('admin.reviews.link.add');
    Route::post('/admin/reviews/link/add', 'Admin\ReviewLinkController@add')->name('admin.reviews.link.add');
    Route::get('/admin/reviews/link/edit/{linkId}', 'Admin\ReviewLinkController@edit')->name('admin.reviews.link.edit');
    Route::post('/admin/reviews/link/edit/{linkId}', 'Admin\ReviewLinkController@edit')->name('admin.reviews.link.edit');
    Route::get('/admin/reviews/link/{report?}', 'Admin\ReviewLinkController@showList')->name('admin.reviews.link.list');

    // User reviews
    Route::get('/admin/reviews/user/edit/{reviewId}', 'Admin\ReviewUserController@edit')->name('admin.reviews.user.edit');
    Route::post('/admin/reviews/user/edit/{reviewId}', 'Admin\ReviewUserController@edit')->name('admin.reviews.user.edit');
    Route::get('/admin/reviews/user/{report?}', 'Admin\ReviewUserController@showList')->name('admin.reviews.user.list');

    // Partner reviews
    Route::get('/admin/reviews/partner/edit/{reviewId}', 'Admin\PartnerReviewController@edit')->name('admin.reviews.partner.edit');
    Route::post('/admin/reviews/partner/edit/{reviewId}', 'Admin\PartnerReviewController@edit')->name('admin.reviews.partner.edit');
    Route::get('/admin/reviews/partner/{report?}', 'Admin\PartnerReviewController@showList')->name('admin.reviews.partner.list');

    // Feed items: Landing
    Route::get('/admin/feed-items', 'Admin\IndexController@feedItemsLanding')->name('admin.feed-items.landing');

    // Feed items: Reviews
    Route::get('/admin/feed-items/reviews/{report?}', 'Admin\FeedItemReviewController@showList')->name('admin.feed-items.reviews.list');
    Route::get('/admin/feed-items/reviews/edit/{linkId}', 'Admin\FeedItemReviewController@edit')->name('admin.feed-items.reviews.edit');
    Route::post('/admin/feed-items/reviews/edit/{linkId}', 'Admin\FeedItemReviewController@edit')->name('admin.feed-items.reviews.edit');

    // Feed items: Games
    Route::get('/admin/feed-items/games/{report?}', 'Admin\FeedItemGameController@showList')->name('admin.feed-items.games.list');
    Route::get('/admin/feed-items/games/edit/{linkId}', 'Admin\FeedItemGameController@edit')->name('admin.feed-items.games.edit');
    Route::post('/admin/feed-items/games/edit/{linkId}', 'Admin\FeedItemGameController@edit')->name('admin.feed-items.games.edit');

    // Feed items: eShop (Europe)
    Route::get('/admin/feed-items/eshop/europe/{report?}', 'Admin\FeedItemEshopEuropeController@showList')->name('admin.feed-items.eshop.europe.list');
    Route::get('/admin/feed-items/eshop/europe/view/{itemId}', 'Admin\FeedItemEshopEuropeController@view')->name('admin.feed-items.eshop.europe.view');
    Route::get('/admin/feed-items/eshop/europe/add-game/{itemId}', 'Admin\FeedItemEshopEuropeController@addGame')->name('admin.feed-items.eshop.europe.add-game');
    Route::post('/admin/feed-items/eshop/europe/add-game/{itemId}', 'Admin\FeedItemEshopEuropeController@addGame')->name('admin.feed-items.eshop.europe.add-game');

    // News
    Route::get('/admin/news/list', 'Admin\NewsController@showList')->name('admin.news.list');
    Route::get('/admin/news/add', 'Admin\NewsController@add')->name('admin.news.add');
    Route::post('/admin/news/add', 'Admin\NewsController@add')->name('admin.news.add');
    Route::get('/admin/news/edit/{newsId}', 'Admin\NewsController@edit')->name('admin.news.edit');
    Route::post('/admin/news/edit/{newsId}', 'Admin\NewsController@edit')->name('admin.news.edit');

    // Users
    Route::get('/admin/user/list', 'Admin\UserController@showList')->name('admin.user.list');
    Route::get('/admin/user/view/{userId}', 'Admin\UserController@showUser')->name('admin.user.view');
    Route::get('/admin/user/edit/{userId}', 'Admin\UserController@editUser')->name('admin.user.edit');
    Route::post('/admin/user/edit/{userId}', 'Admin\UserController@editUser')->name('admin.user.edit');
    Route::get('/admin/user/delete/{userId}', 'Admin\UserController@deleteUser')->name('admin.user.delete');
    Route::post('/admin/user/delete/{userId}', 'Admin\UserController@deleteUser')->name('admin.user.delete');

    // Categorisation
    Route::get('/admin/game-primary-types/list', 'Admin\GamePrimaryTypesController@showList')->name('admin.game-primary-types.list');
    Route::get('/admin/game-primary-types/add', 'Admin\GamePrimaryTypesController@addPrimaryType')->name('admin.game-primary-types.add');
    Route::get('/admin/game-series/list', 'Admin\GameSeriesController@showList')->name('admin.game-series.list');
    Route::get('/admin/game-series/add', 'Admin\GameSeriesController@addGameSeries')->name('admin.game-series.add');

    // Tags
    Route::get('/admin/tag/list', 'Admin\TagController@showList')->name('admin.tag.list');
    Route::get('/admin/tag/add', 'Admin\TagController@addTag')->name('admin.tag.add');
    Route::get('/admin/tag/delete/{tagId}', 'Admin\TagController@deleteTag')->name('admin.tag.delete');
    Route::get('/admin/tag/game/{gameId}/list', 'Admin\TagController@showGameTagList')->name('admin.tag.game.list');
    Route::get('/admin/tag/game/{gameId}/add', 'Admin\TagController@addGameTag')->name('admin.tag.game.add');
    Route::get('/admin/tag/game/{gameId}/remove', 'Admin\TagController@removeGameTag')->name('admin.tag.game.remove');

    // Partners
    Route::get('/admin/partners', 'Admin\PartnersController@landing')->name('admin.partners.landing');

    // Partners: Review sites
    Route::get('/admin/reviews/site', 'Admin\ReviewSiteController@showList')->name('admin.reviews.site.list');
    Route::get('/admin/reviews/site/add', 'Admin\ReviewSiteController@add')->name('admin.reviews.site.add');
    Route::post('/admin/reviews/site/add', 'Admin\ReviewSiteController@add')->name('admin.reviews.site.add');
    Route::get('/admin/reviews/site/edit/{siteId}', 'Admin\ReviewSiteController@edit')->name('admin.reviews.site.edit');
    Route::post('/admin/reviews/site/edit/{siteId}', 'Admin\ReviewSiteController@edit')->name('admin.reviews.site.edit');

    // Partners: Games companies
    Route::get('/admin/partners/games-company/list', 'Admin\GamesCompanyController@showList')->name('admin.partners.games-company.list');
    Route::get('/admin/partners/games-company/add', 'Admin\GamesCompanyController@add')->name('admin.partners.games-company.add');
    Route::post('/admin/partners/games-company/add', 'Admin\GamesCompanyController@add')->name('admin.partners.games-company.add');
    Route::get('/admin/partners/games-company/edit/{partnerId}', 'Admin\GamesCompanyController@edit')->name('admin.partners.games-company.edit');
    Route::post('/admin/partners/games-company/edit/{partnerId}', 'Admin\GamesCompanyController@edit')->name('admin.partners.games-company.edit');
    Route::get('/admin/partners/games-company/delete/{partnerId}', 'Admin\GamesCompanyController@delete')->name('admin.partners.games-company.delete');
    Route::post('/admin/partners/games-company/delete/{partnerId}', 'Admin\GamesCompanyController@delete')->name('admin.partners.games-company.delete');

    // Tools
    Route::get('/admin/tools', 'Admin\ToolsController@landing')->name('admin.tools.landing');
    Route::get('/admin/tools/tool/landing/modular/{commandName}', 'Admin\ToolsController@toolLandingModular')->name('admin.tools.toolLandingModular');
    Route::get('/admin/tools/tool/process/modular/{commandName}', 'Admin\ToolsController@toolProcessModular')->name('admin.tools.toolProcessModular');

    // Stats
    Route::get('/admin/stats', 'Admin\StatsController@landing')->name('admin.stats.landing');
    Route::get('/admin/stats/review/site', 'Admin\StatsController@reviewSite')->name('admin.stats.review.site');
    Route::get('/admin/stats/games/old-developer-multiple', 'Admin\StatsController@oldDeveloperMultiple')->name('admin.stats.games.oldDeveloperMultiple');
    Route::get('/admin/stats/games/old-developer-by-count', 'Admin\StatsController@oldDeveloperByCount')->name('admin.stats.games.oldDeveloperByCount');
    Route::get('/admin/stats/games/old-developer/{developer}', 'Admin\StatsController@oldDeveloperGameList')->name('admin.stats.games.oldDeveloperGameList');
    Route::get('/admin/stats/games/old-publisher-multiple', 'Admin\StatsController@oldPublisherMultiple')->name('admin.stats.games.oldPublisherMultiple');
    Route::get('/admin/stats/games/old-publisher-by-count', 'Admin\StatsController@oldPublisherByCount')->name('admin.stats.games.oldPublisherByCount');
    Route::get('/admin/stats/games/old-publisher/{publisher}', 'Admin\StatsController@oldPublisherGameList')->name('admin.stats.games.oldPublisherGameList');
    Route::get('/admin/stats/games/clear-old-developer', 'Admin\StatsController@clearOldDeveloperField')->name('admin.stats.games.clearOldDeveloperField');
    Route::get('/admin/stats/games/clear-old-publisher', 'Admin\StatsController@clearOldPublisherField')->name('admin.stats.games.clearOldPublisherField');
    Route::get('/admin/stats/games/add-all-new-developers', 'Admin\StatsController@addAllNewDevelopers')->name('admin.stats.games.addAllNewDevelopers');
    Route::get('/admin/stats/games/remove-all-old-developers', 'Admin\StatsController@removeAllOldDevelopers')->name('admin.stats.games.removeAllOldDevelopers');
    Route::get('/admin/stats/games/add-all-new-publishers', 'Admin\StatsController@addAllNewPublishers')->name('admin.stats.games.addAllNewPublishers');
    Route::get('/admin/stats/games/remove-all-old-publishers', 'Admin\StatsController@removeAllOldPublishers')->name('admin.stats.games.removeAllOldPublishers');

});

Auth::routes();



/* Misc redirects */
//Route::get('/lists/released-nintendo-switch-games', 'ListsController@releasedGames')->name('lists.released');
//Route::get('/lists/upcoming-nintendo-switch-games', 'ListsController@upcomingGames')->name('lists.upcoming');

// **** NOTE: THESE NEED TO BE LAST! **** //

/* Blog redirects */
//Route::get('/tag/{tag}/', 'BlogController@redirectTag')->name('blog.redirectTag');
//Route::get('/category/{tag}/', 'BlogController@redirectCategory')->name('blog.redirectCategory');
//Route::get('/{year}/{month}/{day}/{title}/', 'BlogController@redirectPost')->name('blog.redirectPost');
