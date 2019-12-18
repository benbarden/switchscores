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

/*
Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});
*/

// Third-party logins
Route::get('login/twitter', 'Auth\LoginController@redirectToProviderTwitter')->name('auth.login.twitter');
Route::get('login/twitter/callback', 'Auth\LoginController@handleProviderCallbackTwitter')->name('auth.login.twitter.callback');

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

// Mario Maker
Route::get('/mario-maker-levels', 'MarioMakerLevelsController@landing')->name('mario-maker-levels.landing');

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
Route::get('/top-rated/multiplayer', 'TopRatedController@multiplayer')->name('topRated.multiplayer');

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

    // Mario Maker levels
    Route::get('/user/mario-maker-levels/add', 'User\MarioMakerLevelsController@add')->name('user.mario-maker-levels.add');
    Route::post('/user/mario-maker-levels/add', 'User\MarioMakerLevelsController@add')->name('user.mario-maker-levels.add');
    Route::get('/user/mario-maker-levels/{report?}', 'User\MarioMakerLevelsController@showList')->name('user.mario-maker-levels.list');

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



// *************** Staff: General pages *************** //
Route::group(['middleware' => ['auth.staff']], function() {

    Route::get('/staff', 'Staff\IndexController@index')->name('staff.index');

});


// *************** Staff: REVIEWS *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_REVIEWS_MANAGER]], function() {

    Route::get('/staff/reviews/dashboard', 'Staff\Reviews\DashboardController@show')->name('staff.reviews.dashboard');

});


// *************** Staff: CATEGORISATION *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_CATEGORY_MANAGER]], function() {

    Route::get('/staff/categorisation/dashboard', 'Staff\Categorisation\DashboardController@show')->name('staff.categorisation.dashboard');

    // Primary types
    Route::get('/staff/categorisation/game-primary-types/list', 'Staff\Categorisation\GamePrimaryTypesController@showList')->name('staff.categorisation.game-primary-types.list');
    Route::get('/staff/categorisation/game-primary-types/add', 'Staff\Categorisation\GamePrimaryTypesController@addPrimaryType')->name('staff.categorisation.game-primary-types.add');

    // Series
    Route::get('/staff/categorisation/game-series/list', 'Staff\Categorisation\GameSeriesController@showList')->name('staff.categorisation.game-series.list');
    Route::get('/staff/categorisation/game-series/add', 'Staff\Categorisation\GameSeriesController@addGameSeries')->name('staff.categorisation.game-series.add');

    // Genres
    Route::get('/staff/categorisation/genre/list', 'Staff\Categorisation\GenreController@showList')->name('staff.categorisation.genre.list');

    // Tags
    Route::get('/staff/categorisation/tag/list', 'Staff\Categorisation\TagController@showList')->name('staff.categorisation.tag.list');
    Route::get('/staff/categorisation/tag/add', 'Staff\Categorisation\TagController@addTag')->name('staff.categorisation.tag.add');
    Route::get('/staff/categorisation/tag/edit/{tagId}', 'Staff\Categorisation\TagController@editTag')->name('staff.categorisation.tag.edit');
    Route::post('/staff/categorisation/tag/edit/{tagId}', 'Staff\Categorisation\TagController@editTag')->name('staff.categorisation.tag.edit');
    Route::get('/staff/categorisation/tag/delete/{tagId}', 'Staff\Categorisation\TagController@deleteTag')->name('staff.categorisation.tag.delete');
    Route::get('/staff/categorisation/tag/game/{gameId}/list', 'Staff\Categorisation\TagController@showGameTagList')->name('staff.categorisation.tag.game.list');
    Route::get('/staff/categorisation/tag/game/{gameId}/add', 'Staff\Categorisation\TagController@addGameTag')->name('staff.categorisation.tag.game.add');
    Route::get('/staff/categorisation/tag/game/{gameId}/remove', 'Staff\Categorisation\TagController@removeGameTag')->name('staff.categorisation.tag.game.remove');

});


// *************** Staff: GAMES *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_GAMES_MANAGER]], function() {

    Route::get('/staff/games/dashboard', 'Staff\Games\DashboardController@show')->name('staff.games.dashboard');

});


// *************** Staff: PARTNERS *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_PARTNERSHIPS_MANAGER]], function() {

    Route::get('/staff/partners/dashboard', 'Staff\Partners\DashboardController@show')->name('staff.partners.dashboard');

});


// *************** Staff: ESHOP *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_ESHOP_MANAGER]], function() {

    Route::get('/staff/eshop/dashboard', 'Staff\Eshop\DashboardController@show')->name('staff.eshop.dashboard');

    Route::get('/staff/eshop/report/{reportName}', 'Staff\Eshop\ReportController@show')->name('staff.eshop.report');
    Route::get('/staff/eshop/report/{reportName}/game-list/{filterValue}', 'Staff\Eshop\ReportController@gameList')->name('staff.eshop.report.gameList');

    Route::get('/staff/eshop/alerts/errors', 'Staff\Eshop\AlertsController@showErrors')->name('staff.eshop.alerts.errors');
    Route::get('/staff/eshop/alerts/warnings', 'Staff\Eshop\AlertsController@showWarnings')->name('staff.eshop.alerts.warnings');

});


// *************** Staff: WIKIPEDIA *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_WIKIPEDIA_MANAGER]], function() {

    Route::get('/staff/wikipedia/dashboard', 'Staff\Wikipedia\DashboardController@show')->name('staff.wikipedia.dashboard');

    // Wiki updates
    Route::get('/staff/wikipedia/wiki-updates/{report?}', 'Staff\Wikipedia\WikiUpdatesController@showList')->name('staff.wikipedia.wiki-updates.list');
    Route::get('/staff/wikipedia/wiki-updates/edit/{linkId}', 'Staff\Wikipedia\WikiUpdatesController@edit')->name('staff.wikipedia.wiki-updates.edit');
    Route::post('/staff/wikipedia/wiki-updates/edit/{linkId}', 'Staff\Wikipedia\WikiUpdatesController@edit')->name('staff.wikipedia.wiki-updates.edit');

});


// *************** Staff: Admin-only (owner) *************** //
Route::group(['middleware' => ['auth.admin:admin']], function() {

    // Users
    Route::get('/owner/user/list', 'Owner\UserController@showList')->name('owner.user.list');
    Route::get('/owner/user/view/{userId}', 'Owner\UserController@showUser')->name('owner.user.view');
    Route::get('/owner/user/edit/{userId}', 'Owner\UserController@editUser')->name('owner.user.edit');
    Route::post('/owner/user/edit/{userId}', 'Owner\UserController@editUser')->name('owner.user.edit');
    Route::get('/owner/user/delete/{userId}', 'Owner\UserController@deleteUser')->name('owner.user.delete');
    Route::post('/owner/user/delete/{userId}', 'Owner\UserController@deleteUser')->name('owner.user.delete');

    // Stats
    Route::get('/staff/stats/dashboard', 'Staff\Stats\DashboardController@show')->name('staff.stats.dashboard');

    Route::get('/staff/stats/review-site', 'Staff\Stats\ReviewSiteController@show')->name('staff.stats.reviewSite');

    Route::get('/staff/stats/games-company/old-developer-multiple', 'Staff\Stats\GamesCompanyController@oldDeveloperMultiple')->name('staff.stats.gamesCompany.oldDeveloperMultiple');
    Route::get('/staff/stats/games-company/old-developer-by-count', 'Staff\Stats\GamesCompanyController@oldDeveloperByCount')->name('staff.stats.gamesCompany.oldDeveloperByCount');
    Route::get('/staff/stats/games-company/old-developer/{developer}', 'Staff\Stats\GamesCompanyController@oldDeveloperGameList')->name('staff.stats.gamesCompany.oldDeveloperGameList');
    Route::get('/staff/stats/games-company/old-publisher-multiple', 'Staff\Stats\GamesCompanyController@oldPublisherMultiple')->name('staff.stats.gamesCompany.oldPublisherMultiple');
    Route::get('/staff/stats/games-company/old-publisher-by-count', 'Staff\Stats\GamesCompanyController@oldPublisherByCount')->name('staff.stats.gamesCompany.oldPublisherByCount');
    Route::get('/staff/stats/games-company/old-publisher/{publisher}', 'Staff\Stats\GamesCompanyController@oldPublisherGameList')->name('staff.stats.gamesCompany.oldPublisherGameList');
    Route::get('/staff/stats/games-company/clear-old-developer', 'Staff\Stats\GamesCompanyController@clearOldDeveloperField')->name('staff.stats.gamesCompany.clearOldDeveloperField');
    Route::get('/staff/stats/games-company/clear-old-publisher', 'Staff\Stats\GamesCompanyController@clearOldPublisherField')->name('staff.stats.gamesCompany.clearOldPublisherField');
    Route::get('/staff/stats/games-company/add-all-new-developers', 'Staff\Stats\GamesCompanyController@addAllNewDevelopers')->name('staff.stats.gamesCompany.addAllNewDevelopers');
    Route::get('/staff/stats/games-company/remove-all-old-developers', 'Staff\Stats\GamesCompanyController@removeAllOldDevelopers')->name('staff.stats.gamesCompany.removeAllOldDevelopers');
    Route::get('/staff/stats/games-company/add-all-new-publishers', 'Staff\Stats\GamesCompanyController@addAllNewPublishers')->name('staff.stats.gamesCompany.addAllNewPublishers');
    Route::get('/staff/stats/games-company/remove-all-old-publishers', 'Staff\Stats\GamesCompanyController@removeAllOldPublishers')->name('staff.stats.gamesCompany.removeAllOldPublishers');

});



/*
 * Admin - old routes
 * To be gradually moved into Staff routes
 */
Route::group(['middleware' => ['auth.admin:admin']], function() {

    // Index
    Route::get('/admin', 'Admin\DashboardsController@index')->name('admin.index');

    // Games: Core
    Route::get('/admin/games/list/{report?}', 'Admin\GamesController@showList')->name('admin.games.list');

    // Games: Filter list
    Route::get('/admin/games/filter-list/with-tag/{linkTitle}', 'Admin\GamesFilterListController@gamesWithTag')->name('admin.games-filter.games-with-tag');
    Route::get('/admin/games/filter-list/no-tag', 'Admin\GamesFilterListController@gamesWithNoTag')->name('admin.games-filter.games-with-no-tag');
    Route::get('/admin/games/filter-list/no-type-or-tag', 'Admin\GamesFilterListController@gamesWithNoTypeOrTag')->name('admin.games-filter.games-with-no-type-or-tag');
    Route::get('/admin/games/filter-list/with-genre/{linkTitle}', 'Admin\GamesFilterListController@gamesWithGenre')->name('admin.games-filter.games-with-genre');
    Route::get('/admin/games/filter-list/series-title-match/{linkTitle}', 'Admin\GamesFilterListController@gameSeriesTitleMatches')->name('admin.games-filter.game-series-title-matches');
    Route::get('/admin/games/filter-list/tag-title-match/{linkTitle}', 'Admin\GamesFilterListController@gameTagTitleMatches')->name('admin.games-filter.game-tag-title-matches');
    Route::get('/admin/games/filter-list/with-genre-no-primary-type', 'Admin\GamesFilterListController@gamesWithGenresNoPrimaryType')->name('admin.games-filter.games-with-genre-no-primary-type');
    Route::get('/admin/games/filter-list/no-eshop-europe-link', 'Admin\GamesFilterListController@gamesWithNoEshopEuropeLink')->name('admin.games-filter.games-no-eshop-europe-link');

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

    // Approvals (quick format)
    Route::get('/admin/approvals/mario-maker-levels', 'Admin\ApprovalsController@marioMakerLevels')->name('admin.approvals.mario-maker-levels');
    Route::get('/admin/approvals/mario-maker-levels/approve', 'Admin\ApprovalsController@approveMarioMakerLevel')->name('admin.approvals.mario-maker-levels.approve');
    Route::get('/admin/approvals/mario-maker-levels/reject', 'Admin\ApprovalsController@rejectMarioMakerLevel')->name('admin.approvals.mario-maker-levels.reject');

    // Action lists
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
