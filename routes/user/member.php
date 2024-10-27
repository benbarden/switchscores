<?php

use Illuminate\Support\Facades\Route;

// *************** Members *************** //
Route::group(['middleware' => ['auth']], function() {

    // Index
    Route::get('/user', 'User\IndexController@show')->name('user.index');

    // Search (modular)
    Route::match(['get', 'post'], '/user/search-modular/{searchMode}', 'User\SearchModularController@findGame')->name('user.search-modular.find-game');

    // Collection
    Route::get('/user/collection/index', 'User\CollectionController@landing')->name('user.collection.landing');
    Route::get('/user/collection/list/{listOption}', 'User\CollectionController@showList')->name('user.collection.list');
    Route::get('/user/collection/add', 'User\CollectionController@add')->name('user.collection.add');
    Route::post('/user/collection/add', 'User\CollectionController@add')->name('user.collection.add');
    Route::get('/user/collection/edit/{itemId}', 'User\CollectionController@edit')->name('user.collection.edit');
    Route::post('/user/collection/edit/{itemId}', 'User\CollectionController@edit')->name('user.collection.edit');
    Route::get('/user/collection/delete', 'User\CollectionController@delete')->name('user.collection.delete');
    Route::get('/user/collection/category-breakdown', 'User\CollectionController@categoryBreakdown')->name('user.collection.category-breakdown');
    Route::get('/user/collection/top-rated-by-category/{categoryId}', 'User\CollectionController@topRatedByCategory')->name('user.collection.top-rated-by-category');

    // User profile
    Route::get('/user/region/update', 'User\UserProfileController@updateRegion')->name('user.profile.updateRegion');

    // Quick reviews
    Route::match(['get', 'post'], '/user/quick-reviews/add/{gameId}', 'User\QuickReviewController@add')->name('user.quick-reviews.add');
    Route::get('/user/quick-reviews/{report?}', 'User\QuickReviewController@showList')->name('user.quick-reviews.list');

    // Featured games
    Route::match(['get', 'post'], '/user/featured-games/add/{gameId}', 'User\FeaturedGameController@add')->name('user.featured-games.add');

    // Campaigns
    Route::get('/user/campaigns/{campaignId}', 'User\CampaignsController@show')->name('user.campaigns.show');

    // Settings
    Route::get('/user/settings', 'User\SettingsController@show')->name('user.settings');

});
