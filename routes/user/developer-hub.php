<?php

use Illuminate\Support\Facades\Route;

// *************** Developers *************** //
Route::group(['middleware' => ['auth.developer']], function() {

    // *************** Developer hub: Dashboard *************** //
    Route::get('/developer-hub', 'DeveloperHub\IndexController@show')->name('developer-hub.index');

    // Custom tools
    Route::get('/developer-hub/custom-tools/upcoming-games-switch-weekly', 'DeveloperHub\CustomToolsController@upcomingGamesSwitchWeekly')->name('developer-hub.custom-tools.upcoming-games-switch-weekly');

});
