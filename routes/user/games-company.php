<?php

use Illuminate\Support\Facades\Route;

// *************** Games companies *************** //
Route::group(['middleware' => ['auth.gamescompany']], function() {

    // *************** Games companies: Dashboard *************** //
    Route::get('/games-companies', 'GamesCompanies\IndexController@show')->name('games-companies.index');

    Route::get('/games-companies/review-coverage/{gameId}', 'GamesCompanies\ReviewCoverageController@show')->name('games-companies.review-coverage.show');

    // *************** Games companies: Edit details *************** //
    Route::match(['get', 'post'], '/games-companies/edit-details', 'GamesCompanies\ProfileController@edit')->name('games-companies.profile.edit');

});
