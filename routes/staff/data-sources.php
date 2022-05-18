<?php

use Illuminate\Support\Facades\Route;

// *************** Staff: DATA SOURCES *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_DATA_SOURCE_MANAGER]], function() {

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
