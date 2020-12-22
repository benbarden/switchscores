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

use Illuminate\Support\Facades\Route;

// Front page
Route::get('/', 'WelcomeController@show')->name('welcome');

// Third-party logins
Route::get('login/twitter', 'Auth\LoginController@redirectToProviderTwitter')->name('auth.login.twitter');
Route::get('login/twitter/callback', 'Auth\LoginController@handleProviderCallbackTwitter')->name('auth.login.twitter.callback');

// Static content
Route::get('/about', 'AboutController@show')->name('about');
Route::get('/privacy', 'PrivacyController@show')->name('privacy');

// Main game pages
Route::match(['get', 'post'], '/games', 'GamesController@landing')->name('games.landing');
Route::get('/games/recent', 'GamesController@recentReleases')->name('games.recentReleases');
Route::get('/games/upcoming', 'GamesController@upcomingReleases')->name('games.upcomingReleases');
Route::get('/games/on-sale', 'GamesController@gamesOnSale')->name('games.onSale');
Route::match(['get', 'post'], '/games/search', 'Games\SearchController@show')->name('games.search');

// Browse by...
Route::get('/games/by-title', 'GamesBrowseController@byTitleLanding')->name('games.browse.byTitle.landing');
Route::get('/games/by-title/{letter}', 'GamesBrowseController@byTitlePage')->name('games.browse.byTitle.page');

Route::get('/games/by-category', 'GamesBrowseController@byCategoryLanding')->name('games.browse.byCategory.landing');
Route::get('/games/by-category/{category}', 'GamesBrowseController@byCategoryPage')->name('games.browse.byCategory.page');

Route::get('/games/by-series', 'GamesBrowseController@bySeriesLanding')->name('games.browse.bySeries.landing');
Route::get('/games/by-series/{series}', 'GamesBrowseController@bySeriesPage')->name('games.browse.bySeries.page');

Route::get('/games/by-tag', 'GamesBrowseController@byTagLanding')->name('games.browse.byTag.landing');
Route::get('/games/by-tag/{tag}', 'GamesBrowseController@byTagPage')->name('games.browse.byTag.page');

Route::get('/games/by-date', 'GamesBrowseController@byDateLanding')->name('games.browse.byDate.landing');
Route::get('/games/by-date/{date}', 'GamesBrowseController@byDatePage')->name('games.browse.byDate.page');

// Primary type redirects
Route::get('/games/by-type', 'GamesBrowseController@byPrimaryTypeLanding')->name('games.browse.byPrimaryType.landing');
Route::get('/games/by-type/{primaryType}', 'GamesBrowseController@byPrimaryTypePage')->name('games.browse.byPrimaryType.page');

// These must be after the game redirects
Route::get('/games/{id}', 'GamesController@showId')->name('game.showId');
Route::get('/games/{id}/{title}', 'GamesController@show')->name('game.show');

/* Top Rated */
Route::get('/top-rated', 'TopRatedController@landing')->name('topRated.landing');
Route::get('/top-rated/all-time', 'TopRatedController@allTime')->name('topRated.allTime');
Route::get('/top-rated/by-year/{year}', 'TopRatedController@byYear')->name('topRated.byYear');
Route::get('/top-rated/multiplayer', 'TopRatedController@multiplayer')->name('topRated.multiplayer');

/* Reviews */
Route::get('/reviews/{year}', 'ReviewsController@landingByYear')->name('reviews.landing.byYear');
Route::get('/reviews', 'ReviewsController@landing')->name('reviews.landing');
Route::get('/reviews/site/{linkTitle}', 'ReviewsController@reviewSite')->name('reviews.site');

/* Partners */
Route::get('/partners', 'PartnersController@landing')->name('partners.landing');
Route::get('/partners/review-sites', 'PartnersController@reviewSites')->name('partners.review-sites');
Route::get('/partners/developers-publishers', 'PartnersController@developersPublishers')->name('partners.developers-publishers');

Route::get('/partners/games-company/{linkTitle}', 'PartnersController@showGamesCompany')->name('partners.detail.games-company');

/* News */
Route::get('/news', 'NewsController@landing')->name('news.landing');
Route::get('/news/category/{linkName}', 'NewsController@categoryLanding')->name('news.category.landing');
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

// *************** Members *************** //
Route::group(['middleware' => ['auth']], function() {

    // Index
    Route::get('/user', 'User\IndexController@show')->name('user.index');

    // Database help
    Route::get('/user/database-help/index', 'User\DatabaseHelpController@landing')->name('user.database-help.index');
    Route::get('/user/database-help/games-without-categories', 'User\DatabaseHelpController@gamesWithoutCategories')->name('user.database-help.games-without-categories');
    Route::get('/user/database-help/games-without-categories/{year}', 'User\DatabaseHelpController@gamesWithoutCategoriesByYear')->name('user.database-help.games-without-categories.byYear');
    Route::match(['get', 'post'], '/user/database-help/games-without-categories/submit-game-category-suggestion/{gameId}',
        'User\DatabaseHelpController@submitGameCategorySuggestion')->name('user.database-help.games-without-categories.submit-game-category-suggestion');

    // Collection
    Route::get('/user/collection/index', 'User\CollectionController@landing')->name('user.collection.landing');
    Route::get('/user/collection/list/{listOption}', 'User\CollectionController@showList')->name('user.collection.list');
    Route::get('/user/collection/add', 'User\CollectionController@add')->name('user.collection.add');
    Route::post('/user/collection/add', 'User\CollectionController@add')->name('user.collection.add');
    Route::get('/user/collection/edit/{itemId}', 'User\CollectionController@edit')->name('user.collection.edit');
    Route::post('/user/collection/edit/{itemId}', 'User\CollectionController@edit')->name('user.collection.edit');
    Route::get('/user/collection/delete', 'User\CollectionController@delete')->name('user.collection.delete');

    // User profile
    Route::get('/user/region/update', 'User\UserProfileController@updateRegion')->name('user.profile.updateRegion');

    // Quick reviews
    Route::get('/user/quick-reviews/add', 'User\QuickReviewController@add')->name('user.quick-reviews.add');
    Route::post('/user/quick-reviews/add', 'User\QuickReviewController@add')->name('user.quick-reviews.add');
    Route::get('/user/quick-reviews/{report?}', 'User\QuickReviewController@showList')->name('user.quick-reviews.list');

    // Games companies: Games list
    Route::get('/user/games-list/{report}', 'User\GamesListController@landing')->name('user.games-list.landing');

    // Settings
    Route::get('/user/settings', 'User\SettingsController@show')->name('user.settings');

});


// *************** Reviewers *************** //
Route::group(['middleware' => ['auth.reviewer']], function() {

    // *************** Reviewers: Dashboard *************** //
    Route::get('/reviewers', 'Reviewers\IndexController@show')->name('reviewers.index');

    // *************** Reviewers: Manually add reviews *************** //
    Route::match(['get', 'post'], '/reviewers/review-feed-item/find-game', 'Reviewers\ReviewFeedItemController@findGame')->name('reviewers.review-feed-item.find-game');
    Route::match(['get', 'post'], '/reviewers/reviews/feed-item/add/{gameId}', 'Reviewers\ReviewFeedItemController@add')->name('reviewers.review-feed-item.add');
    Route::match(['get', 'post'], '/reviewers/reviews/feed-item/edit/{itemId}', 'Reviewers\ReviewFeedItemController@edit')->name('reviewers.review-feed-item.edit');

    // *************** Reviewers: Campaigns *************** //
    Route::get('/reviewers/campaigns/{campaignId}', 'Reviewers\CampaignsController@show')->name('reviewers.campaigns.show');

    // *************** Reviewers: Games *************** //
    Route::get('/reviewers/games/{gameId}', 'Reviewers\GamesController@show')->name('reviewers.games.show');

    // *************** Reviewers: Stats *************** //
    Route::get('/reviewers/stats', 'Reviewers\StatsController@landing')->name('reviewers.stats.landing');

    // *************** Reviewers: Feed health *************** //
    Route::get('/reviewers/feed-health', 'Reviewers\FeedHealthController@landing')->name('reviewers.feed-health.landing');
    Route::get('/reviewers/feed-health/by-process-status/{status}', 'Reviewers\FeedHealthController@byProcessStatus')->name('reviewers.feed-health.by-process-status');
    Route::get('/reviewers/feed-health/by-parse-status/{status}', 'Reviewers\FeedHealthController@byParseStatus')->name('reviewers.feed-health.by-parse-status');

    // *************** Reviewers: Review links *************** //
    Route::get('/reviewers/reviews/{report?}', 'Reviewers\ReviewLinkController@landing')->name('reviewers.reviews.landing');

    // *************** Reviewers: Unranked games *************** //
    Route::get('/reviewers/unranked-games', 'Reviewers\UnrankedGamesController@landing')->name('reviewers.unranked-games.landing');
    Route::get('/reviewers/unranked-games/{mode}/{filter}', 'Reviewers\UnrankedGamesController@showList')->name('reviewers.unranked-games.list');

});


// *************** Developers *************** //
Route::group(['middleware' => ['auth.developer']], function() {

    // *************** Developer hub: Dashboard *************** //
    Route::get('/developer-hub', 'DeveloperHub\IndexController@show')->name('developer-hub.index');

    // Custom tools
    Route::get('/developer-hub/custom-tools/upcoming-games-switch-weekly', 'DeveloperHub\CustomToolsController@upcomingGamesSwitchWeekly')->name('developer-hub.custom-tools.upcoming-games-switch-weekly');

});


// *************** Staff: General pages *************** //
Route::group(['middleware' => ['auth.staff']], function() {

    Route::get('/staff', 'Staff\IndexController@index')->name('staff.index');

});


// *************** Staff: GAMES *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_GAMES_MANAGER]], function() {

    Route::get('/staff/games/dashboard', 'Staff\Games\DashboardController@show')->name('staff.games.dashboard');

    // Find a game
    Route::match(['get', 'post'], '/staff/games/find', 'Staff\Games\FindController@show')->name('staff.games.find');

    // Search
    Route::match(['get', 'post'], '/staff/games/search', 'Staff\Games\SearchController@show')->name('staff.games.search');

    // Games: Detail
    Route::get('/staff/games/detail/{gameId}', 'Staff\Games\GamesDetailController@show')->name('staff.games.detail');
    Route::get('/staff/games/detail/full-audit/{game}', 'Staff\Games\GamesDetailController@showFullAudit')->name('staff.games.detail.fullAudit');
    Route::get('/staff/games/detail/{gameId}/update-eshop-data', 'Staff\Games\GamesDetailController@updateEshopData')->name('staff.games.detail.updateEshopData');
    Route::get('/staff/games/detail/{gameId}/redownload-packshots', 'Staff\Games\GamesDetailController@redownloadPackshots')->name('staff.games.detail.redownloadPackshots');

    // Games: Add, edit, delete
    Route::match(['get', 'post'], '/staff/games/add', 'Staff\Games\GamesEditorController@add')->name('staff.games.add');
    Route::match(['get', 'post'], '/staff/games/edit/{gameId}', 'Staff\Games\GamesEditorController@edit')->name('staff.games.edit');
    Route::match(['get', 'post'], '/staff/games/edit-nintendo-co-uk/{gameId}', 'Staff\Games\GamesEditorController@editNintendoCoUk')->name('staff.games.editNintendoCoUk');
    Route::match(['get', 'post'], '/staff/games/delete/{gameId}', 'Staff\Games\GamesEditorController@delete')->name('staff.games.delete');
    Route::get('/staff/games/release', 'Staff\Games\GamesEditorController@releaseGame')->name('staff.games.release');

    // Game import rules
    Route::match(['get', 'post'], '/staff/games/{gameId}/import-rule-eshop/edit', 'Staff\Games\ImportRuleEshopController@edit')->name('staff.games.import-rule-eshop.edit');
    Route::match(['get', 'post'], '/staff/games/{gameId}/import-rule-wikipedia/edit', 'Staff\Games\ImportRuleWikipediaController@edit')->name('staff.games.import-rule-wikipedia.edit');

    // Game lists
    Route::get('/staff/games/list/games-to-release', 'Staff\Games\GamesListController@gamesToRelease')->name('staff.games.list.games-to-release');
    Route::get('/staff/games/list/recently-added', 'Staff\Games\GamesListController@recentlyAdded')->name('staff.games.list.recently-added');
    Route::get('/staff/games/list/recently-released', 'Staff\Games\GamesListController@recentlyReleased')->name('staff.games.list.recently-released');
    Route::get('/staff/games/list/upcoming-games', 'Staff\Games\GamesListController@upcomingGames')->name('staff.games.list.upcoming-games');
    Route::get('/staff/games/list/no-eu-release-date', 'Staff\Games\GamesListController@noEuReleaseDate')->name('staff.games.list.no-eu-release-date');
    Route::get('/staff/games/list/no-eshop-price', 'Staff\Games\GamesListController@noEshopPrice')->name('staff.games.list.no-eshop-price');
    Route::get('/staff/games/list/no-video-url', 'Staff\Games\GamesListController@noVideoUrl')->name('staff.games.list.no-video-url');
    Route::get('/staff/games/list/no-amazon-uk-link', 'Staff\Games\GamesListController@noAmazonUkLink')->name('staff.games.list.no-amazon-uk-link');
    Route::get('/staff/games/list/no-nintendo-co-uk-link', 'Staff\Games\GamesListController@noNintendoCoUkLink')->name('staff.games.list.no-nintendo-co-uk-link');
    Route::get('/staff/games/list/broken-nintendo-co-uk-link', 'Staff\Games\GamesListController@brokenNintendoCoUkLink')->name('staff.games.list.broken-nintendo-co-uk-link');
    Route::get('/staff/games/list/by-category/{category}', 'Staff\Games\GamesListController@byCategory')->name('staff.games.list.by-category');
    Route::get('/staff/games/list/by-series/{gameSeries}', 'Staff\Games\GamesListController@bySeries')->name('staff.games.list.by-series');
    Route::get('/staff/games/list/by-tag/{tag}', 'Staff\Games\GamesListController@byTag')->name('staff.games.list.by-tag');

    // Games: Tools
    Route::match(['get', 'post'], '/staff/games/tools/update-game-calendar-stats', 'Staff\Games\ToolsController@updateGameCalendarStats')->name('staff.games.tools.updateGameCalendarStats');

});


// *************** Staff: REVIEWS *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_REVIEWS_MANAGER]], function() {

    Route::get('/staff/reviews/dashboard', 'Staff\Reviews\DashboardController@show')->name('staff.reviews.dashboard');

    // Review links
    Route::match(['get', 'post'], '/staff/reviews/link/add', 'Staff\Reviews\ReviewLinkController@add')->name('staff.reviews.link.add');
    Route::match(['get', 'post'], '/staff/reviews/link/edit/{linkId}', 'Staff\Reviews\ReviewLinkController@edit')->name('staff.reviews.link.edit');
    Route::match(['get', 'post'], '/staff/reviews/link/delete/{linkId}', 'Staff\Reviews\ReviewLinkController@delete')->name('staff.reviews.link.delete');
    Route::get('/staff/reviews/link/{report?}', 'Staff\Reviews\ReviewLinkController@showList')->name('staff.reviews.link.list');

    // Quick reviews
    Route::match(['get', 'post'], '/staff/reviews/quick-reviews/edit/{reviewId}', 'Staff\Reviews\QuickReviewController@edit')->name('staff.reviews.quick-reviews.edit');
    Route::match(['get', 'post'], '/staff/reviews/quick-reviews/delete/{reviewId}', 'Staff\Reviews\QuickReviewController@delete')->name('staff.reviews.quick-reviews.delete');
    Route::get('/staff/reviews/quick-reviews/{report?}', 'Staff\Reviews\QuickReviewController@showList')->name('staff.reviews.quick-reviews.list');

    // Review feed items
    Route::get('/staff/reviews/feed-items/{report?}', 'Staff\Reviews\FeedItemsController@showList')->name('staff.reviews.feed-items.list');
    Route::get('/staff/reviews/feed-items/by-process-status/{status}', 'Staff\Reviews\FeedItemsController@byProcessStatus')->name('staff.reviews.feed-items.by-process-status');
    Route::match(['get', 'post'], '/staff/reviews/feed-items/edit/{linkId}', 'Staff\Reviews\FeedItemsController@edit')->name('staff.reviews.feed-items.edit');

    // Review feed imports
    Route::get('/staff/reviews/feed-imports', 'Staff\Reviews\FeedImportsController@show')->name('staff.reviews.feed-imports.list');
    Route::get('/staff/reviews/feed-imports/{feedImport}/items', 'Staff\Reviews\FeedImportsController@showItemList')->name('staff.reviews.feed-imports.item-list');

    // Review highlights
    Route::get('/staff/reviews/highlights', 'Staff\Reviews\HighlightsController@show')->name('staff.reviews.highlights');

    // Review campaigns
    Route::get('/staff/reviews/campaigns', 'Staff\Reviews\CampaignsController@show')->name('staff.reviews.campaigns');
    Route::match(['get', 'post'], '/staff/reviews/campaigns/add', 'Staff\Reviews\CampaignsController@add')->name('staff.reviews.campaigns.add');
    Route::match(['get', 'post'], '/staff/reviews/campaigns/edit/{campaignId}', 'Staff\Reviews\CampaignsController@edit')->name('staff.reviews.campaigns.edit');
    Route::match(['get', 'post'], '/staff/reviews/campaigns/edit-games/{campaignId}', 'Staff\Reviews\CampaignsController@editGames')->name('staff.reviews.campaigns.editGames');

    // Unranked lists
    Route::get('/staff/reviews/unranked/review-count', 'Staff\Reviews\UnrankedController@reviewCountLanding')->name('staff.reviews.unranked.review-count-landing');
    Route::get('/staff/reviews/unranked/review-count/{reviewCount}/list', 'Staff\Reviews\UnrankedController@reviewCountList')->name('staff.reviews.unranked.review-count-list');
    Route::get('/staff/reviews/unranked/release-year', 'Staff\Reviews\UnrankedController@releaseYearLanding')->name('staff.reviews.unranked.release-year-landing');
    Route::get('/staff/reviews/unranked/release-year/{releaseYear}/list', 'Staff\Reviews\UnrankedController@releaseYearList')->name('staff.reviews.unranked.release-year-list');

    // Reviews: Tools
    Route::match(['get', 'post'], '/staff/reviews/tools/run-feed-importer', 'Staff\Reviews\ToolsController@runFeedImporter')->name('staff.reviews.tools.runFeedImporter');
    Route::match(['get', 'post'], '/staff/reviews/tools/run-feed-parser', 'Staff\Reviews\ToolsController@runFeedParser')->name('staff.reviews.tools.runFeedParser');
    Route::match(['get', 'post'], '/staff/reviews/tools/run-feed-review-generator', 'Staff\Reviews\ToolsController@runFeedReviewGenerator')->name('staff.reviews.tools.runFeedReviewGenerator');
    Route::match(['get', 'post'], '/staff/reviews/tools/update-game-ranks', 'Staff\Reviews\ToolsController@updateGameRanks')->name('staff.reviews.tools.updateGameRanks');
    Route::match(['get', 'post'], '/staff/reviews/tools/update-game-review-stats', 'Staff\Reviews\ToolsController@updateGameReviewStats')->name('staff.reviews.tools.updateGameReviewStats');

});


// *************** Staff: CATEGORISATION *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_CATEGORY_MANAGER]], function() {

    Route::get('/staff/categorisation/dashboard', 'Staff\Categorisation\DashboardController@show')->name('staff.categorisation.dashboard');
    Route::get('/staff/categorisation/title-match/category/{category}', 'Staff\Categorisation\DashboardController@categoryTitleMatch')->name('staff.categorisation.title-match.category');
    Route::get('/staff/categorisation/title-match/series/{gameSeries}', 'Staff\Categorisation\DashboardController@seriesTitleMatch')->name('staff.categorisation.title-match.series');
    Route::get('/staff/categorisation/title-match/tag/{tag}', 'Staff\Categorisation\DashboardController@tagTitleMatch')->name('staff.categorisation.title-match.tag');

    // Member suggestions
    Route::get('/staff/categorisation/game-category-suggestions', 'Staff\Categorisation\GameCategorySuggestionController@show')->name('staff.categorisation.game-category-suggestions.index');
    Route::get('/staff/categorisation/game-category-suggestions/approve', 'Staff\Categorisation\GameCategorySuggestionController@approve')->name('staff.categorisation.game-category-suggestions.approve');
    Route::get('/staff/categorisation/game-category-suggestions/deny', 'Staff\Categorisation\GameCategorySuggestionController@deny')->name('staff.categorisation.game-category-suggestions.deny');

    // Categories
    Route::get('/staff/categorisation/category/list', 'Staff\Categorisation\CategoryController@showList')->name('staff.categorisation.category.list');
    Route::match(['get', 'post'], '/staff/categorisation/category/add', 'Staff\Categorisation\CategoryController@addCategory')->name('staff.categorisation.category.add');
    Route::match(['get', 'post'], '/staff/categorisation/category/edit/{categoryId}', 'Staff\Categorisation\CategoryController@editCategory')->name('staff.categorisation.category.edit');
    Route::match(['get', 'post'], '/staff/categorisation/category/delete/{categoryId}', 'Staff\Categorisation\CategoryController@deleteCategory')->name('staff.categorisation.category.delete');

    // Series
    Route::get('/staff/categorisation/game-series/list', 'Staff\Categorisation\GameSeriesController@showList')->name('staff.categorisation.game-series.list');
    Route::get('/staff/categorisation/game-series/add', 'Staff\Categorisation\GameSeriesController@addGameSeries')->name('staff.categorisation.game-series.add');

    // Tags
    Route::get('/staff/categorisation/tag/list', 'Staff\Categorisation\TagController@showList')->name('staff.categorisation.tag.list');
    Route::get('/staff/categorisation/tag/add', 'Staff\Categorisation\TagController@addTag')->name('staff.categorisation.tag.add');
    Route::match(['get', 'post'], '/staff/categorisation/tag/edit/{tagId}', 'Staff\Categorisation\TagController@editTag')->name('staff.categorisation.tag.edit');
    Route::get('/staff/categorisation/tag/delete/{tagId}', 'Staff\Categorisation\TagController@deleteTag')->name('staff.categorisation.tag.delete');
    Route::get('/staff/categorisation/tag/game/{gameId}/list', 'Staff\Categorisation\TagController@showGameTagList')->name('staff.categorisation.tag.game.list');
    Route::get('/staff/categorisation/tag/game/{gameId}/add', 'Staff\Categorisation\TagController@addGameTag')->name('staff.categorisation.tag.game.add');
    Route::get('/staff/categorisation/tag/game/{gameId}/remove', 'Staff\Categorisation\TagController@removeGameTag')->name('staff.categorisation.tag.game.remove');

    // Migrations
    Route::get('/staff/categorisation/migrations/category/games-with-one-genre', 'Staff\Categorisation\MigrationsCategoryController@gamesWithOneGenre')->name('staff.categorisation.migrations.category.games-with-one-genre');
    Route::get('/staff/categorisation/migrations/category/games-with-named-genre-and-one-other/{genre}', 'Staff\Categorisation\MigrationsCategoryController@gamesWithNamedGenreAndOneOther')->name('staff.categorisation.migrations.category.games-with-named-genre-and-one-other');
    Route::get('/staff/categorisation/migrations/category/all-games-with-no-category', 'Staff\Categorisation\MigrationsCategoryController@allGamesWithNoCategory')->name('staff.categorisation.migrations.category.all-games-with-no-category');

});

// *************** Staff: NEWS *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_NEWS_MANAGER]], function() {

    Route::get('/staff/news/dashboard', 'Staff\News\DashboardController@show')->name('staff.news.dashboard');

    // List
    Route::get('/staff/news/list', 'Staff\News\ListController@show')->name('staff.news.list');

    // Editor
    Route::get('/staff/news/add', 'Staff\News\EditorController@add')->name('staff.news.add');
    Route::post('/staff/news/add', 'Staff\News\EditorController@add')->name('staff.news.add');
    Route::match(['get', 'post'], '/staff/news/edit/{newsId}', 'Staff\News\EditorController@edit')->name('staff.news.edit');

    // Categories
    Route::get('/staff/news/category/list', 'Staff\News\CategoryController@showList')->name('staff.news.category.list');
    Route::match(['get', 'post'], '/staff/news/category/add', 'Staff\News\CategoryController@add')->name('staff.news.category.add');
    Route::match(['get', 'post'], '/staff/news/category/edit/{newsCategoryId}', 'Staff\News\CategoryController@edit')->name('staff.news.category.edit');

});


// *************** Staff: PARTNERS *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_PARTNERSHIPS_MANAGER]], function() {

    Route::get('/staff/partners/dashboard', 'Staff\Partners\DashboardController@show')->name('staff.partners.dashboard');

    // Partners: Review sites
    Route::get('/staff/partners/review-site', 'Staff\Partners\ReviewSiteController@showList')->name('staff.reviews.site.list');
    Route::match(['get', 'post'], '/staff/partners/review-site/add', 'Staff\Partners\ReviewSiteController@add')->name('staff.reviews.site.add');
    Route::match(['get', 'post'], '/staff/partners/review-site/edit/{siteId}', 'Staff\Partners\ReviewSiteController@edit')->name('staff.reviews.site.edit');

    // Partners: Games companies
    Route::get('/staff/partners/games-company/list', 'Staff\Partners\GamesCompanyController@showList')->name('staff.partners.games-company.list');
    Route::match(['get', 'post'], '/staff/partners/games-company/add', 'Staff\Partners\GamesCompanyController@add')->name('staff.partners.games-company.add');
    Route::match(['get', 'post'], '/staff/partners/games-company/edit/{partnerId}', 'Staff\Partners\GamesCompanyController@edit')->name('staff.partners.games-company.edit');
    Route::match(['get', 'post'], '/staff/partners/games-company/delete/{partnerId}', 'Staff\Partners\GamesCompanyController@delete')->name('staff.partners.games-company.delete');

    Route::get('/staff/partners/games-company/show/{partner}', 'Staff\Partners\GamesCompanyController@show')->name('staff.partners.games-company.show');

    Route::get('/staff/partners/games-company/devs-with-unranked-games', 'Staff\Partners\GamesCompanyController@devsWithUnrankedGames')->name('staff.partners.games-company.devs-with-unranked-games');
    Route::get('/staff/partners/games-company/pubs-with-unranked-games', 'Staff\Partners\GamesCompanyController@pubsWithUnrankedGames')->name('staff.partners.games-company.pubs-with-unranked-games');

    Route::get('/staff/partners/games-company/without-twitter-ids', 'Staff\Partners\GamesCompanyController@withoutTwitterIds')->name('staff.partners.games-company.without-twitter-ids');
    Route::get('/staff/partners/games-company/without-website-urls', 'Staff\Partners\GamesCompanyController@withoutWebsiteUrls')->name('staff.partners.games-company.without-website-urls');
    Route::get('/staff/partners/games-company/duplicate-twitter-ids', 'Staff\Partners\GamesCompanyController@duplicateTwitterIds')->name('staff.partners.games-company.duplicate-twitter-ids');
    Route::get('/staff/partners/games-company/duplicate-website-urls', 'Staff\Partners\GamesCompanyController@duplicateWebsiteUrls')->name('staff.partners.games-company.duplicate-website-urls');

    // Partners: Outreach
    Route::get('/staff/partners/outreach/list/{partner?}', 'Staff\Partners\OutreachController@showList')->name('staff.partners.outreach.list');
    Route::match(['get', 'post'], '/staff/partners/outreach/add', 'Staff\Partners\OutreachController@add')->name('staff.partners.outreach.add');
    Route::match(['get', 'post'], '/staff/partners/outreach/edit/{partnerOutreach}', 'Staff\Partners\OutreachController@edit')->name('staff.partners.outreach.edit');

    // Partners: Data cleanup
    Route::get('/staff/partners/data-cleanup/games-with-missing-developer', 'Staff\Partners\DataCleanupController@gamesWithMissingDeveloper')->name('staff.partners.data-cleanup.games-with-missing-developer');
    Route::get('/staff/partners/data-cleanup/games-with-missing-publisher', 'Staff\Partners\DataCleanupController@gamesWithMissingPublisher')->name('staff.partners.data-cleanup.games-with-missing-publisher');

    // Partners: Tools
    Route::match(['get', 'post'], '/staff/partners/tools/partner-update-fields', 'Staff\Partners\ToolsController@partnerUpdateFields')->name('staff.partners.tools.partnerUpdateFields');

});


// *************** Staff: DATA SOURCES *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_DATA_SOURCE_MANAGER]], function() {

    Route::get('/staff/data-sources/dashboard', 'Staff\DataSources\DashboardController@show')->name('staff.data-sources.dashboard');

    // Data sources: Lists
    Route::get('/staff/data-sources/{sourceId}/list-raw', 'Staff\DataSources\DataSourceRawController@show')->name('staff.data-sources.list-raw');
    Route::get('/staff/data-sources/{sourceId}/list-raw/{itemId}/view', 'Staff\DataSources\DataSourceRawController@view')->name('staff.data-sources.list-raw.view');

    // Data sources: Ignore list
    Route::get('/staff/data-sources/ignore/add', 'Staff\DataSources\DataSourceIgnoreController@addToIgnoreList')->name('staff.data-sources.ignore.addToIgnoreList');
    Route::get('/staff/data-sources/ignore/remove', 'Staff\DataSources\DataSourceIgnoreController@removeFromIgnoreList')->name('staff.data-sources.ignore.removeFromIgnoreList');

    // Data sources: Differences
    Route::get('/staff/data-sources/differences/nintendo-co-uk/eu-release-date', 'Staff\DataSources\DifferencesController@nintendoCoUkEuReleaseDate')->name('staff.data-sources.differences.nintendo-co-uk.eu-release-date');
    Route::get('/staff/data-sources/differences/nintendo-co-uk/price', 'Staff\DataSources\DifferencesController@nintendoCoUkPrice')->name('staff.data-sources.differences.nintendo-co-uk.price');
    Route::get('/staff/data-sources/differences/nintendo-co-uk/players', 'Staff\DataSources\DifferencesController@nintendoCoUkPlayers')->name('staff.data-sources.differences.nintendo-co-uk.players');
    Route::get('/staff/data-sources/differences/nintendo-co-uk/publishers', 'Staff\DataSources\DifferencesController@nintendoCoUkPublishers')->name('staff.data-sources.differences.nintendo-co-uk.publishers');
    Route::get('/staff/data-sources/differences/nintendo-co-uk/genres', 'Staff\DataSources\DifferencesController@nintendoCoUkGenres')->name('staff.data-sources.differences.nintendo-co-uk.genres');
    Route::get('/staff/data-sources/differences/wikipedia/eu-release-date', 'Staff\DataSources\DifferencesController@wikipediaEuReleaseDate')->name('staff.data-sources.differences.wikipedia.eu-release-date');
    Route::get('/staff/data-sources/differences/wikipedia/us-release-date', 'Staff\DataSources\DifferencesController@wikipediaUsReleaseDate')->name('staff.data-sources.differences.wikipedia.us-release-date');
    Route::get('/staff/data-sources/differences/wikipedia/jp-release-date', 'Staff\DataSources\DifferencesController@wikipediaJpReleaseDate')->name('staff.data-sources.differences.wikipedia.jp-release-date');
    Route::get('/staff/data-sources/differences/wikipedia/developers', 'Staff\DataSources\DifferencesController@wikipediaDevelopers')->name('staff.data-sources.differences.wikipedia.developers');
    Route::get('/staff/data-sources/differences/wikipedia/publishers', 'Staff\DataSources\DifferencesController@wikipediaPublishers')->name('staff.data-sources.differences.wikipedia.publishers');
    Route::get('/staff/data-sources/differences/wikipedia/genres', 'Staff\DataSources\DifferencesController@wikipediaGenres')->name('staff.data-sources.differences.wikipedia.genres');
    Route::get('/staff/data-sources/differences/apply-change', 'Staff\DataSources\DifferencesController@applyChange')->name('staff.data-sources.differences.apply-change');
    Route::get('/staff/data-sources/differences/ignore-change', 'Staff\DataSources\DifferencesController@ignoreChange')->name('staff.data-sources.differences.ignore-change');

    // Data sources: Nintendo.co.uk
    Route::get('/staff/data-sources/nintendo-co-uk/unlinked', 'Staff\DataSources\DataSourceParsedController@nintendoCoUkUnlinkedItems')->name('staff.data-sources.nintendo-co-uk.unlinked');
    Route::get('/staff/data-sources/nintendo-co-uk/ignored', 'Staff\DataSources\DataSourceParsedController@nintendoCoUkIgnoredItems')->name('staff.data-sources.nintendo-co-uk.ignored');
    Route::match(['get', 'post'], '/staff/data-sources/nintendo-co-uk/add-game/{itemId}', 'Staff\DataSources\DataSourceParsedController@addGameNintendoCoUk')->name('staff.data-sources.nintendo-co-uk.add-game');

    // Tools: Nintendo.co.uk
    Route::match(['get', 'post'], '/staff/data-sources/tools/nintendo-co-uk/import-parse-link', 'Staff\DataSources\ToolsController@nintendoCoUkImportParseLink')->name('staff.data-sources.tools.nintendo-co-uk.importParseLink');
    Route::match(['get', 'post'], '/staff/data-sources/tools/nintendo-co-uk/update-games', 'Staff\DataSources\ToolsController@nintendoCoUkUpdateGames')->name('staff.data-sources.tools.nintendo-co-uk.updateGames');
    Route::match(['get', 'post'], '/staff/data-sources/tools/nintendo-co-uk/download-images', 'Staff\DataSources\ToolsController@nintendoCoUkDownloadImages')->name('staff.data-sources.tools.nintendo-co-uk.downloadImages');

    // Data sources: Wikipedia
    Route::get('/staff/data-sources/wikipedia/unlinked', 'Staff\DataSources\DataSourceParsedController@wikipediaUnlinkedItems')->name('staff.data-sources.wikipedia.unlinked');
    Route::get('/staff/data-sources/wikipedia/ignored', 'Staff\DataSources\DataSourceParsedController@wikipediaIgnoredItems')->name('staff.data-sources.wikipedia.ignored');
    Route::match(['get', 'post'], '/staff/data-sources/wikipedia/unlinked/add-link/{itemId}', 'Staff\DataSources\DataSourceParsedController@wikipediaAddLink')->name('staff.data-sources.wikipedia.add-link');

    // Tools: Wikipedia
    Route::match(['get', 'post'], '/staff/data-sources/tools/wikipedia/import-parse-link', 'Staff\DataSources\ToolsController@wikipediaImportParseLink')->name('staff.data-sources.tools.wikipedia.importParseLink');
    Route::match(['get', 'post'], '/staff/data-sources/tools/wikipedia/update-games', 'Staff\DataSources\ToolsController@wikipediaUpdateGames')->name('staff.data-sources.tools.wikipedia.updateGames');

});


// *************** Staff: DATA QUALITY *************** //
Route::group(['middleware' => ['auth.admin:admin']], function() {

    Route::get('/staff/data-quality/dashboard', 'Staff\DataQuality\DashboardController@show')->name('staff.data-quality.dashboard');

    Route::get('/staff/data-quality/duplicate-reviews', 'Staff\DataQuality\DashboardController@duplicateReviews')->name('staff.data-quality.duplicate-reviews');

    Route::get('/staff/data-quality/category/dashboard', 'Staff\DataQuality\CategoryController@dashboard')->name('staff.data-quality.category.dashboard');
    Route::get('/staff/data-quality/category/games-with-categories/{year}/{month}', 'Staff\DataQuality\CategoryController@gamesWithCategories')->name('staff.data-quality.games-with-categories');
    Route::get('/staff/data-quality/category/games-without-categories/{year}/{month}', 'Staff\DataQuality\CategoryController@gamesWithoutCategories')->name('staff.data-quality.games-without-categories');

});


// *************** Staff: Admin-only (owner) *************** //
Route::group(['middleware' => ['auth.admin:admin']], function() {

    // Users
    Route::get('/owner/user/list', 'Owner\UserController@showList')->name('owner.user.list');
    Route::get('/owner/user/view/{userId}', 'Owner\UserController@showUser')->name('owner.user.view');
    Route::match(['get', 'post'], '/owner/user/edit/{userId}', 'Owner\UserController@editUser')->name('owner.user.edit');
    Route::match(['get', 'post'], '/owner/user/delete/{userId}', 'Owner\UserController@deleteUser')->name('owner.user.delete');

    // Audit
    Route::get('/owner/audit/index', 'Owner\AuditController@index')->name('owner.audit.index');
    Route::get('/owner/audit/{reportName}', 'Owner\AuditController@showReport')->name('owner.audit.report');

    // Stats
    Route::get('/staff/stats/dashboard', 'Staff\Stats\DashboardController@show')->name('staff.stats.dashboard');

    Route::get('/staff/stats/review-site', 'Staff\Stats\ReviewSiteController@show')->name('staff.stats.reviewSite');

});



/*
 * Admin - old routes
 * To be gradually moved into Staff routes
 */
Route::group(['middleware' => ['auth.admin:admin']], function() {

    // Games: Title hashes
    Route::get('/admin/games-title-hash/list/{gameId?}', 'Admin\GamesTitleHashController@showList')->name('admin.games-title-hash.list');
    Route::match(['get', 'post'], '/admin/games-title-hash/add', 'Admin\GamesTitleHashController@add')->name('admin.games-title-hash.add');
    Route::match(['get', 'post'], '/admin/games-title-hash/edit/{itemId}', 'Admin\GamesTitleHashController@edit')->name('admin.games-title-hash.edit');
    Route::match(['get', 'post'], '/admin/games-title-hash/delete/{itemId}', 'Admin\GamesTitleHashController@delete')->name('admin.games-title-hash.delete');

    // Games partner links
    Route::get('/admin/game/partner/{gameId}/list', 'Admin\GamePartnerController@showGamePartners')->name('admin.game.partner.list');
    Route::get('/admin/game/partner/create-new-company', 'Admin\GamePartnerController@createNewCompany')->name('admin.game.partner.createNewCompany');
    Route::get('/admin/game/developer/{gameId}/add', 'Admin\GamePartnerController@addGameDeveloper')->name('admin.game.developer.add');
    Route::get('/admin/game/developer/{gameId}/remove', 'Admin\GamePartnerController@removeGameDeveloper')->name('admin.game.developer.remove');
    Route::get('/admin/game/publisher/{gameId}/add', 'Admin\GamePartnerController@addGamePublisher')->name('admin.game.publisher.add');
    Route::get('/admin/game/publisher/{gameId}/remove', 'Admin\GamePartnerController@removeGamePublisher')->name('admin.game.publisher.remove');

});

Auth::routes();
