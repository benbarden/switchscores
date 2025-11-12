<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Staff\News\DashboardController;

// *************** Staff: NEWS *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_NEWS_MANAGER]], function() {

    Route::get('/staff/news/dashboard', 'Staff\News\DashboardController@show')->name('staff.news.dashboard');

    // List
    Route::get('/staff/news/list', 'Staff\News\ListController@show')->name('staff.news.list');

    // Editor
    Route::get('/staff/news/add', 'Staff\News\EditorController@add')->name('staff.news.add');
    Route::post('/staff/news/add', 'Staff\News\EditorController@add')->name('staff.news.add');
    Route::match(['get', 'post'], '/staff/news/edit/{newsId}', 'Staff\News\EditorController@edit')->name('staff.news.edit');
    Route::match(['get', 'post'], '/staff/news/delete/{newsId}', 'Staff\News\EditorController@delete')->name('staff.news.delete');

    // Categories
    Route::get('/staff/news/category/list', 'Staff\News\CategoryController@showList')->name('staff.news.category.list');
    Route::match(['get', 'post'], '/staff/news/category/add', 'Staff\News\CategoryController@add')->name('staff.news.category.add');
    Route::match(['get', 'post'], '/staff/news/category/edit/{newsCategoryId}', 'Staff\News\CategoryController@edit')->name('staff.news.category.edit');

    Route::prefix('staff/news')->group(function () {
        Route::get('/bucket/{bucket}', [DashboardController::class, 'bucket'])
            ->name('staff.news.bucket');
        Route::post('/bucket/{bucket}/enqueue', [DashboardController::class, 'enqueue'])
            ->name('staff.news.enqueue');
        Route::get('/generate-bucket-draft/{bucket}', [DashboardController::class, 'generateBucketDraft'])
            ->name('staff.news.generateBucketDraft');
        Route::get('/generate-custom-draft/{bucket}', [DashboardController::class, 'generateCustomDraft'])
            ->name('staff.news.generateCustomDraft');
    });
});
