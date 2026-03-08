<?php

use Illuminate\Support\Facades\Route;

// *************** Members *************** //
Route::prefix('members')->middleware(['auth'])->name('members.')->group(function () {

    // Index
    Route::get('/', 'Members\IndexController@show')->name('index');

    // Search (modular)
    Route::match(['get', 'post'], '/search-modular/{searchMode}', 'Members\SearchModularController@findGame')->name('search-modular.find-game');

    // Collection (view only)
    Route::get('/collection/index', 'Members\CollectionController@landing')->name('collection.landing');
    Route::get('/collection/list/{listOption}', 'Members\CollectionController@showList')->name('collection.list');
    Route::get('/collection/category-breakdown', 'Members\CollectionController@categoryBreakdown')->name('collection.category-breakdown');
    Route::get('/collection/top-rated-by-category/{categoryId}', 'Members\CollectionController@topRatedByCategory')->name('collection.top-rated-by-category');
    Route::get('/collection/quick-add', 'Members\CollectionController@quickAdd')->name('collection.quickAdd');

    // Game finder
    Route::get('/find-game', 'Members\GameFinderController@index')->name('game-finder.index');

    // Wishlist (view only)
    Route::get('/wishlist', 'Members\WishlistController@index')->name('wishlist.index');

    // Ignored/hidden games (view only)
    Route::get('/ignored-games', 'Members\IgnoredGamesController@index')->name('ignored-games.index');

    // Quick reviews (view only)
    Route::get('/quick-reviews/{report?}', 'Members\QuickReviewController@showList')->name('quick-reviews.list');

    // Routes requiring email verification
    Route::middleware(['verified'])->group(function () {
        // Collection (modify)
        Route::get('/collection/add', 'Members\CollectionController@add')->name('collection.add');
        Route::post('/collection/add', 'Members\CollectionController@add');
        Route::get('/collection/edit/{itemId}', 'Members\CollectionController@edit')->name('collection.edit');
        Route::post('/collection/edit/{itemId}', 'Members\CollectionController@edit');
        Route::get('/collection/delete', 'Members\CollectionController@delete')->name('collection.delete');

        // Wishlist (modify)
        Route::post('/wishlist/add/{gameId}', 'Members\WishlistController@add')->name('wishlist.add');
        Route::post('/wishlist/remove/{gameId}', 'Members\WishlistController@remove')->name('wishlist.remove');

        // Ignored/hidden games (modify)
        Route::post('/ignored-games/add/{gameId}', 'Members\IgnoredGamesController@add')->name('ignored-games.add');
        Route::post('/ignored-games/remove/{gameId}', 'Members\IgnoredGamesController@remove')->name('ignored-games.remove');

        // Quick reviews (add)
        Route::match(['get', 'post'], '/quick-reviews/add/{gameId}', 'Members\QuickReviewController@add')->name('quick-reviews.add');

        // Featured games
        Route::match(['get', 'post'], '/featured-games/add/{gameId}', 'Members\FeaturedGameController@add')->name('featured-games.add');
    });

    // Campaigns
    Route::get('/campaigns/{campaignId}', 'Members\CampaignsController@show')->name('campaigns.show');

    // Settings
    Route::get('/settings', 'Members\SettingsController@show')->name('settings');
    Route::post('/settings', 'Members\SettingsController@update')->name('settings.update');

    // Email verification
    Route::post('/email/send-verification', 'Members\EmailVerificationController@sendVerification')->name('email.send-verification');
    Route::get('/email/verify/{id}', 'Members\EmailVerificationController@verify')->name('email.verify');

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
