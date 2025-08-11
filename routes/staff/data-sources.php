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
    Route::get('/staff/data-sources/differences/apply-change', 'Staff\DataSources\DifferencesController@applyChange')->name('staff.data-sources.differences.apply-change');
    Route::get('/staff/data-sources/differences/ignore-change', 'Staff\DataSources\DifferencesController@ignoreChange')->name('staff.data-sources.differences.ignore-change');

    // Data sources: Nintendo.co.uk
    Route::get('/staff/data-sources/nintendo-co-uk/unlinked', 'Staff\DataSources\DataSourceParsedController@nintendoCoUkUnlinkedItems')->name('staff.data-sources.nintendo-co-uk.unlinked');
    Route::get('/staff/data-sources/nintendo-co-uk/ignored', 'Staff\DataSources\DataSourceParsedController@nintendoCoUkIgnoredItems')->name('staff.data-sources.nintendo-co-uk.ignored');
    Route::match(['get', 'post'], '/staff/data-sources/nintendo-co-uk/add-game/{itemId}', 'Staff\DataSources\DataSourceParsedController@addGameNintendoCoUk')->name('staff.data-sources.nintendo-co-uk.add-game');

});
