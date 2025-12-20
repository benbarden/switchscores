<?php

use Illuminate\Support\Facades\Route;

use App\Models\UserRole;

use App\Http\Controllers\Staff\Games\DashboardController;
use App\Http\Controllers\Staff\Games\ReleaseHubController;
use App\Http\Controllers\Staff\Games\SearchController;
use App\Http\Controllers\Staff\Games\GamesDetailController;
use App\Http\Controllers\Staff\Games\GamesEditorController;
use App\Http\Controllers\Staff\Games\BulkEditorController;
use App\Http\Controllers\Staff\Games\ImportRuleEshopController;
use App\Http\Controllers\Staff\Games\GamesListController;
use App\Http\Controllers\Staff\Games\FeaturedGameController;
use App\Http\Controllers\Staff\Games\GamesTitleHashController;
use App\Http\Controllers\Staff\Games\GamesPartnerController;
use App\Http\Controllers\Staff\Games\GamesTagController;

// *************** Staff: GAMES *************** //
Route::group([
    'middleware' => ['auth.staff', 'check.user.role:'.UserRole::ROLE_GAMES_MANAGER],
    'prefix' => 'staff/games',
    'as' => 'staff.games.',
], function () {

    // ---- Dashboard and overview ----
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'show')->name('dashboard');
        Route::get('stats', 'stats')->name('stats');
    });

    // ---- Release hub ----
    Route::controller(ReleaseHubController::class)->group(function () {
        Route::get('release-hub', 'show')->name('release-hub.show');
        Route::post('release-hub/add', 'store')->name('release-hub.add');
        Route::post('release-hub/toggle/{id}', 'toggleRelease')->name('release-hub.toggle');
        Route::post('release-hub/reorder', 'reorder')->name('release-hub.reorder');
    });

    // ---- Search ----
    Route::match(['get', 'post'], 'search', [SearchController::class, 'show'])
        ->name('search');

    // ---- Game detail and audit ----
    Route::controller(GamesDetailController::class)->group(function () {
        Route::get('detail/{gameId}', 'show')->name('detail');
        Route::get('detail/full-audit/{game}', 'showFullAudit')->name('detail.fullAudit');
        Route::get('detail/{gameId}/update-eshop-data', 'updateEshopData')->name('detail.updateEshopData');
        Route::get('detail/{gameId}/redownload-packshots', 'redownloadPackshots')->name('detail.redownloadPackshots');
    });

    // ---- Game add / edit / delete ----
    Route::controller(GamesEditorController::class)->group(function () {
        Route::match(['get', 'post'], 'add', 'add')->name('add');
        Route::match(['get', 'post'], 'edit/{gameId}', 'edit')->name('edit');
        Route::match(['get', 'post'], 'edit-nintendo-co-uk/{gameId}', 'editNintendoCoUk')->name('editNintendoCoUk');
        Route::match(['get', 'post'], 'delete/{gameId}', 'delete')->name('delete');
    });

    // ---- Bulk add and import ----
    Route::controller(BulkEditorController::class)->group(function () {
        Route::match(['get', 'post'], 'bulk-add', 'bulkAdd')->name('bulk-add.add');
        Route::match(['get', 'post'], 'bulk-add-complete/{errors?}', 'bulkAddComplete')->name('bulk-add.complete');

        Route::match(['get', 'post'], 'import-from-csv', 'importFromCsv')->name('import-from-csv.import');
        Route::match(['get', 'post'], 'import-from-csv/{errors?}', 'importFromCsvComplete')->name('import-from-csv.complete');

        Route::match(['get', 'post'], 'bulk-edit/edit-predefined-list/{editMode}', 'editList')
            ->name('bulk-edit.editPredefinedList');
        Route::match(['get', 'post'], 'bulk-edit/edit-game-id-list/{editMode}/{gameIdList}', 'editList')
            ->name('bulk-edit.editGameIdList');
        Route::match(['get', 'post'], 'bulk-edit/eshop-upcoming-crosscheck/{consoleId}', 'eshopUpcomingCrosscheck')
            ->name('bulk-edit.eshopUpcomingCrosscheck');
    });

    // ---- Import rules ----
    Route::match(['get', 'post'], '{gameId}/import-rule-eshop/edit',
        [ImportRuleEshopController::class, 'edit']
    )->name('import-rule-eshop.edit');

    // ---- Game lists ----
    Route::controller(GamesListController::class)->group(function () {
        Route::get('list/{listType}/{param1?}/{param2?}', 'showList')->name('list.showList');
        Route::get('export/{listType}/{param1?}/{param2?}', 'exportCsv')->name('list.export');
    });

    // ---- Featured games ----
    Route::controller(FeaturedGameController::class)->group(function () {
        Route::get('featured-games/list', 'showList')->name('featured-games.list');
        Route::get('featured-games/accept-item', 'acceptItem')->name('featured-games.acceptItem');
        Route::get('featured-games/reject-item', 'rejectItem')->name('featured-games.rejectItem');
        Route::get('featured-games/archive-item', 'archiveItem')->name('featured-games.archiveItem');
    });

    // ---- Title hashes ----
    Route::controller(GamesTitleHashController::class)->group(function () {
        Route::get('title-hash/list/{gameId?}', 'showList')->name('title-hash.list');
        Route::match(['get', 'post'], 'title-hash/add', 'add')->name('title-hash.add');
        Route::match(['get', 'post'], 'title-hash/edit/{itemId}', 'edit')->name('title-hash.edit');
        Route::match(['get', 'post'], 'title-hash/delete/{itemId}', 'delete')->name('title-hash.delete');
    });

    // ---- Partners and companies ----
    Route::controller(GamesPartnerController::class)->group(function () {
        Route::get('partner/{gameId}/list', 'showGamePartners')->name('partner.list');
        Route::get('partner/create-new-company', 'createNewCompany')->name('partner.createNewCompany');

        Route::get('developer/{gameId}/add', 'addGameDeveloper')->name('developer.add');
        Route::get('developer/{gameId}/remove', 'removeGameDeveloper')->name('developer.remove');

        Route::get('publisher/{gameId}/add', 'addGamePublisher')->name('publisher.add');
        Route::get('publisher/{gameId}/remove', 'removeGamePublisher')->name('publisher.remove');
    });

    // ---- Tags ----
    Route::match(['get', 'post'], 'tag/{gameId}/edit',
        [GamesTagController::class, 'edit']
    )->name('tag.edit');

});
