<?php

use Illuminate\Support\Facades\Route;

// *************** Staff: GAMES *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_GAMES_MANAGER]], function() {

    Route::get('/staff/games/dashboard', 'Staff\Games\DashboardController@show')->name('staff.games.dashboard');

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

    // Games: Bulk add
    Route::match(['get', 'post'], '/staff/games/bulk-add', 'Staff\Games\BulkEditorController@bulkAdd')->name('staff.games.bulk-add.add');
    Route::match(['get', 'post'], '/staff/games/bulk-add-complete/{errors?}', 'Staff\Games\BulkEditorController@bulkAddComplete')->name('staff.games.bulk-add.complete');

    // Games: Bulk add
    Route::match(['get', 'post'], '/staff/games/import-from-csv', 'Staff\Games\BulkEditorController@importFromCsv')->name('staff.games.import-from-csv.import');
    Route::match(['get', 'post'], '/staff/games/import-from-csv/{errors?}', 'Staff\Games\BulkEditorController@importFromCsvComplete')->name('staff.games.import-from-csv.complete');

    // Games: Bulk editing
    Route::match(['get', 'post'], '/staff/games/bulk-edit/edit-predefined-list/{editMode}', 'Staff\Games\BulkEditorController@editList')->name('staff.games.bulk-edit.editPredefinedList');
    Route::match(['get', 'post'], '/staff/games/bulk-edit/edit-game-id-list/{editMode}/{gameIdList}', 'Staff\Games\BulkEditorController@editList')->name('staff.games.bulk-edit.editGameIdList');
    Route::match(['get', 'post'], '/staff/games/bulk-edit/eshop-upcoming-crosscheck', 'Staff\Games\BulkEditorController@eshopUpcomingCrosscheck')->name('staff.games.bulk-edit.eshopUpcomingCrosscheck');

    // Game import rules
    Route::match(['get', 'post'], '/staff/games/{gameId}/import-rule-eshop/edit', 'Staff\Games\ImportRuleEshopController@edit')->name('staff.games.import-rule-eshop.edit');

    // Game lists
    Route::get('/staff/games/list/games-to-release', 'Staff\Games\GamesListController@gamesToRelease')->name('staff.games.list.games-to-release');
    Route::get('/staff/games/list/recently-added', 'Staff\Games\GamesListController@recentlyAdded')->name('staff.games.list.recently-added');
    Route::get('/staff/games/list/recently-released', 'Staff\Games\GamesListController@recentlyReleased')->name('staff.games.list.recently-released');
    Route::get('/staff/games/list/upcoming-games', 'Staff\Games\GamesListController@upcomingGames')->name('staff.games.list.upcoming-games');
    Route::get('/staff/games/list/upcoming-eshop-crosscheck', 'Staff\Games\GamesListController@upcomingEshopCrosscheck')->name('staff.games.list.upcoming-eshop-crosscheck');
    Route::get('/staff/games/list/no-category-excluding-low-quality', 'Staff\Games\GamesListController@noCategoryExcludingLowQuality')->name('staff.games.list.no-category-excluding-low-quality');
    Route::get('/staff/games/list/no-category-all', 'Staff\Games\GamesListController@noCategoryAll')->name('staff.games.list.no-category-all');
    Route::get('/staff/games/list/no-category-with-collection', 'Staff\Games\GamesListController@noCategoryWithCollection')->name('staff.games.list.no-category-with-collection');
    Route::get('/staff/games/list/no-eu-release-date', 'Staff\Games\GamesListController@noEuReleaseDate')->name('staff.games.list.no-eu-release-date');
    Route::get('/staff/games/list/no-eshop-price', 'Staff\Games\GamesListController@noEshopPrice')->name('staff.games.list.no-eshop-price');
    Route::get('/staff/games/list/no-video-type', 'Staff\Games\GamesListController@noVideoType')->name('staff.games.list.no-video-type');
    Route::get('/staff/games/list/no-amazon-uk-link', 'Staff\Games\GamesListController@noAmazonUkLink')->name('staff.games.list.no-amazon-uk-link');
    Route::get('/staff/games/list/no-amazon-us-link', 'Staff\Games\GamesListController@noAmazonUsLink')->name('staff.games.list.no-amazon-us-link');
    Route::get('/staff/games/list/no-nintendo-co-uk-link', 'Staff\Games\GamesListController@noNintendoCoUkLink')->name('staff.games.list.no-nintendo-co-uk-link');
    Route::get('/staff/games/list/broken-nintendo-co-uk-link', 'Staff\Games\GamesListController@brokenNintendoCoUkLink')->name('staff.games.list.broken-nintendo-co-uk-link');
    Route::get('/staff/games/list/format-option/{format}/{option?}', 'Staff\Games\GamesListController@formatOptionList')->name('staff.games.list.format-option');
    Route::get('/staff/games/list/by-category/{category}', 'Staff\Games\GamesListController@byCategory')->name('staff.games.list.by-category');
    Route::get('/staff/games/list/by-series/{gameSeries}', 'Staff\Games\GamesListController@bySeries')->name('staff.games.list.by-series');
    Route::get('/staff/games/list/by-tag/{tag}', 'Staff\Games\GamesListController@byTag')->name('staff.games.list.by-tag');

    // Games: Tools
    Route::match(['get', 'post'], '/staff/games/tools/update-game-calendar-stats', 'Staff\Games\ToolsController@updateGameCalendarStats')->name('staff.games.tools.updateGameCalendarStats');

    // Featured games
    Route::get('/staff/games/featured-games/list', 'Staff\Games\FeaturedGameController@showList')->name('staff.games.featured-games.list');
    Route::get('/staff/games/featured-games/accept-item', 'Staff\Games\FeaturedGameController@acceptItem')->name('staff.games.featured-games.acceptItem');
    Route::get('/staff/games/featured-games/reject-item', 'Staff\Games\FeaturedGameController@rejectItem')->name('staff.games.featured-games.rejectItem');
    Route::get('/staff/games/featured-games/archive-item', 'Staff\Games\FeaturedGameController@archiveItem')->name('staff.games.featured-games.archiveItem');

    // Games: Title hashes
    Route::get('/staff/games/title-hash/list/{gameId?}', 'Staff\Games\GamesTitleHashController@showList')->name('staff.games-title-hash.list');
    Route::match(['get', 'post'], '/staff/games/title-hash/add', 'Staff\Games\GamesTitleHashController@add')->name('staff.games-title-hash.add');
    Route::match(['get', 'post'], '/staff/games/title-hash/edit/{itemId}', 'Staff\Games\GamesTitleHashController@edit')->name('staff.games-title-hash.edit');
    Route::match(['get', 'post'], '/staff/games/title-hash/delete/{itemId}', 'Staff\Games\GamesTitleHashController@delete')->name('staff.games-title-hash.delete');

    // Games partner links
    Route::get('/staff/games/partner/{gameId}/list', 'Staff\Games\GamesPartnerController@showGamePartners')->name('staff.game.partner.list');
    Route::get('/staff/games/partner/create-new-company', 'Staff\Games\GamesPartnerController@createNewCompany')->name('staff.game.partner.createNewCompany');
    Route::get('/staff/games/developer/{gameId}/add', 'Staff\Games\GamesPartnerController@addGameDeveloper')->name('staff.game.developer.add');
    Route::get('/staff/games/developer/{gameId}/remove', 'Staff\Games\GamesPartnerController@removeGameDeveloper')->name('staff.game.developer.remove');
    Route::get('/staff/games/publisher/{gameId}/add', 'Staff\Games\GamesPartnerController@addGamePublisher')->name('staff.game.publisher.add');
    Route::get('/staff/games/publisher/{gameId}/remove', 'Staff\Games\GamesPartnerController@removeGamePublisher')->name('staff.game.publisher.remove');

    // Games: Tags
    Route::match(['get', 'post'], '/staff/games/tag/{gameId}/edit', 'Staff\Games\GamesTagController@edit')->name('staff.game.tag.edit');

});
