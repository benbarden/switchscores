<?php

use Illuminate\Support\Facades\Route;

// *************** Staff: DATA QUALITY *************** //
Route::group(['middleware' => ['auth.admin:admin']], function() {

    Route::get('/staff/data-quality/dashboard', 'Staff\DataQuality\DashboardController@show')->name('staff.data-quality.dashboard');

    Route::get('/staff/data-quality/duplicate-reviews', 'Staff\DataQuality\DashboardController@duplicateReviews')->name('staff.data-quality.duplicate-reviews');

    Route::get('/staff/data-quality/category/dashboard', 'Staff\DataQuality\CategoryController@dashboard')->name('staff.data-quality.category.dashboard');
    Route::get('/staff/data-quality/category/games-with-categories/{year}/{month}', 'Staff\DataQuality\CategoryController@gamesWithCategories')->name('staff.data-quality.games-with-categories');
    Route::get('/staff/data-quality/category/games-without-categories/{year}/{month}', 'Staff\DataQuality\CategoryController@gamesWithoutCategories')->name('staff.data-quality.games-without-categories');

});

// *************** Staff: Admin-only (owner) *************** //
Route::group(['middleware' => ['auth.admin:admin']], function() {

    // Users
    Route::get('/owner/user/list', 'Owner\UserController@showList')->name('owner.user.list');
    Route::get('/owner/user/view/{userId}', 'Owner\UserController@showUser')->name('owner.user.view');
    Route::match(['get', 'post'], '/owner/user/edit/{userId}', 'Owner\UserController@editUser')->name('owner.user.edit');
    Route::match(['get', 'post'], '/owner/user/delete/{userId}', 'Owner\UserController@deleteUser')->name('owner.user.delete');

    // Audit
    Route::get('/owner/audit/index', 'Owner\AuditController@index')->name('owner.audit.index');
    Route::get('/owner/audit/{reportName}', 'Owner\AuditController@showReport')->name('owner.audit.report');

    // Stats
    Route::get('/staff/stats/dashboard', 'Staff\Stats\DashboardController@show')->name('staff.stats.dashboard');

    Route::get('/staff/stats/review-site', 'Staff\Stats\ReviewSiteController@show')->name('staff.stats.reviewSite');
    Route::get('/staff/stats/review-link/{partnerId}', 'Staff\Stats\ReviewLinkController@show')->name('staff.stats.reviewLink');

});
