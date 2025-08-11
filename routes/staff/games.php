<?php

use Illuminate\Support\Facades\Route;

use App\Models\UserRole;

use App\Http\Controllers\Staff\Games\ReleaseHubController;

// *************** Staff: GAMES *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.UserRole::ROLE_GAMES_MANAGER]], function() {

    Route::get('/staff/games/dashboard', 'Staff\Games\DashboardController@show')->name('staff.games.dashboard');

    // Release hub
    Route::get('/staff/games/release-hub', [ReleaseHubController::class, 'show'])->name('staff.games.release-hub.show');
    Route::post('/staff/games/release-hub/add', [ReleaseHubController::class, 'store'])->name('staff.games.release-hub.add');
    Route::post('/staff/games/release-hub/toggle/{id}', [ReleaseHubController::class, 'toggleRelease'])->name('staff.games.release-hub.toggle');
    Route::post('/staff/games/release-hub/reorder', [ReleaseHubController::class, 'reorder'])->name('staff.games.release-hub.reorder');

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

    // Games: Bulk add
    Route::match(['get', 'post'], '/staff/games/bulk-add', 'Staff\Games\BulkEditorController@bulkAdd')->name('staff.games.bulk-add.add');
    Route::match(['get', 'post'], '/staff/games/bulk-add-complete/{errors?}', 'Staff\Games\BulkEditorController@bulkAddComplete')->name('staff.games.bulk-add.complete');

    // Games: Bulk add
    Route::match(['get', 'post'], '/staff/games/import-from-csv', 'Staff\Games\BulkEditorController@importFromCsv')->name('staff.games.import-from-csv.import');
    Route::match(['get', 'post'], '/staff/games/import-from-csv/{errors?}', 'Staff\Games\BulkEditorController@importFromCsvComplete')->name('staff.games.import-from-csv.complete');

    // Games: Bulk editing
    Route::match(['get', 'post'], '/staff/games/bulk-edit/edit-predefined-list/{editMode}', 'Staff\Games\BulkEditorController@editList')->name('staff.games.bulk-edit.editPredefinedList');
    Route::match(['get', 'post'], '/staff/games/bulk-edit/edit-game-id-list/{editMode}/{gameIdList}', 'Staff\Games\BulkEditorController@editList')->name('staff.games.bulk-edit.editGameIdList');
    Route::match(['get', 'post'], '/staff/games/bulk-edit/eshop-upcoming-crosscheck/{consoleId}', 'Staff\Games\BulkEditorController@eshopUpcomingCrosscheck')->name('staff.games.bulk-edit.eshopUpcomingCrosscheck');

    // Game import rules
    Route::match(['get', 'post'], '/staff/games/{gameId}/import-rule-eshop/edit', 'Staff\Games\ImportRuleEshopController@edit')->name('staff.games.import-rule-eshop.edit');

    // Game lists v2
    Route::get('/staff/games/list/{listType}/{param1?}/{param2?}', 'Staff\Games\GamesListController@showList')->name('staff.games.list.showList');

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
