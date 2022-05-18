<?php

use Illuminate\Support\Facades\Route;

// *************** Staff: NEWS *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_NEWS_MANAGER]], function() {

    Route::get('/staff/news/dashboard', 'Staff\News\DashboardController@show')->name('staff.news.dashboard');

    // List
    Route::get('/staff/news/list', 'Staff\News\ListController@show')->name('staff.news.list');

    // Editor
    Route::get('/staff/news/add', 'Staff\News\EditorController@add')->name('staff.news.add');
    Route::post('/staff/news/add', 'Staff\News\EditorController@add')->name('staff.news.add');
    Route::match(['get', 'post'], '/staff/news/edit/{newsId}', 'Staff\News\EditorController@edit')->name('staff.news.edit');

    // Categories
    Route::get('/staff/news/category/list', 'Staff\News\CategoryController@showList')->name('staff.news.category.list');
    Route::match(['get', 'post'], '/staff/news/category/add', 'Staff\News\CategoryController@add')->name('staff.news.category.add');
    Route::match(['get', 'post'], '/staff/news/category/edit/{newsCategoryId}', 'Staff\News\CategoryController@edit')->name('staff.news.category.edit');

});
