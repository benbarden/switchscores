<?php

use Illuminate\Support\Facades\Route;

// *************** Developers *************** //
Route::group(['middleware' => ['auth.developer']], function() {

    // *************** Developer hub: Dashboard *************** //
    Route::get('/developer-hub', 'DeveloperHub\IndexController@show')->name('developer-hub.index');

    // Custom tools
    Route::get('/developer-hub/custom-tools/upcoming-games-switch-weekly', 'DeveloperHub\CustomToolsController@upcomingGamesSwitchWeekly')->name('developer-hub.custom-tools.upcoming-games-switch-weekly');

    // API
    Route::get('/developer-hub/api/guide', 'DeveloperHub\ApiController@guide')->name('developer-hub.api.guide');
    Route::get('/developer-hub/api/methods', 'DeveloperHub\ApiController@methods')->name('developer-hub.api.methods');

    Route::get('/developer-hub/api/tokens', 'DeveloperHub\ApiController@tokens')->name('developer-hub.api.tokens');
    Route::get('/developer-hub/api/tokens/create', 'DeveloperHub\ApiController@createToken')->name('developer-hub.api.tokens.create');
    Route::get('/developer-hub/api/tokens/delete/{tokenId}', 'DeveloperHub\ApiController@deleteToken')->name('developer-hub.api.tokens.delete');

});
