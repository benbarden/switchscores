<?php

use Illuminate\Support\Facades\Route;

// *************** Games companies *************** //
Route::prefix('games-companies')->middleware(['auth.gamescompany'])->name('games-companies.')->group(function () {

    // *************** Games companies: Dashboard *************** //
    Route::get('/', 'Members\GamesCompanies\IndexController@show')->name('index');

    Route::get('/review-coverage/{gameId}', 'Members\GamesCompanies\ReviewCoverageController@show')->name('review-coverage.show');

    // *************** Games companies: Edit details *************** //
    Route::match(['get', 'post'], '/edit-details', 'Members\GamesCompanies\ProfileController@edit')->name('profile.edit');

});
