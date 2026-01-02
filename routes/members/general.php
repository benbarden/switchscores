<?php

use Illuminate\Support\Facades\Route;

// *************** Members *************** //
Route::prefix('members')->middleware(['auth'])->name('members.')->group(function () {

    // Index
    Route::get('/', 'Members\IndexController@show')->name('index');

    // Search (modular)
    Route::match(['get', 'post'], '/search-modular/{searchMode}', 'Members\SearchModularController@findGame')->name('search-modular.find-game');

    // Collection
    Route::get('/collection/index', 'Members\CollectionController@landing')->name('collection.landing');
    Route::get('/collection/list/{listOption}', 'Members\CollectionController@showList')->name('collection.list');
    Route::get('/collection/add', 'Members\CollectionController@add')->name('collection.add');
    Route::post('/collection/add', 'Members\CollectionController@add')->name('collection.add');
    Route::get('/collection/edit/{itemId}', 'Members\CollectionController@edit')->name('collection.edit');
    Route::post('/collection/edit/{itemId}', 'Members\CollectionController@edit')->name('collection.edit');
    Route::get('/collection/delete', 'Members\CollectionController@delete')->name('collection.delete');
    Route::get('/collection/category-breakdown', 'Members\CollectionController@categoryBreakdown')->name('collection.category-breakdown');
    Route::get('/collection/top-rated-by-category/{categoryId}', 'Members\CollectionController@topRatedByCategory')->name('collection.top-rated-by-category');

    // Collection v2 - POC
    Route::controller('Members\CollectionController')->group(function () {
        Route::get('/collection/quick-add', 'quickAdd')->name('collection.quickAdd');
    });

    // Quick reviews
    Route::match(['get', 'post'], '/quick-reviews/add/{gameId}', 'Members\QuickReviewController@add')->name('quick-reviews.add');
    Route::get('/quick-reviews/{report?}', 'Members\QuickReviewController@showList')->name('quick-reviews.list');

    // Featured games
    Route::match(['get', 'post'], '/featured-games/add/{gameId}', 'Members\FeaturedGameController@add')->name('featured-games.add');

    // Campaigns
    Route::get('/campaigns/{campaignId}', 'Members\CampaignsController@show')->name('campaigns.show');

    // Settings
    Route::get('/settings', 'Members\SettingsController@show')->name('settings');

    // Developers
    // *************** Developer hub: Dashboard *************** //
    Route::get('/developers', 'Members\Developers\IndexController@index')->name('developers.index');

    // Custom tools
    Route::get('/developers/switch-weekly', 'Members\Developers\IndexController@switchWeekly')->name('developers.switch-weekly');
    Route::get('/developers/hanafuda-report', 'Members\Developers\IndexController@hanafudaReport')->name('developers.hanafuda-report');

    // API
    Route::get('/developers/api/guide', 'Members\Developers\ApiController@guide')->name('developers.api.guide');
    Route::get('/developers/api/methods', 'Members\Developers\ApiController@methods')->name('developers.api.methods');

    Route::get('/developers/api/tokens', 'Members\Developers\ApiController@tokens')->name('developers.api.tokens');
    Route::get('/developers/api/tokens/create', 'Members\Developers\ApiController@createToken')->name('developers.api.tokens.create');
    Route::get('/developers/api/tokens/delete/{tokenId}', 'Members\Developers\ApiController@deleteToken')->name('developers.api.tokens.delete');

});
