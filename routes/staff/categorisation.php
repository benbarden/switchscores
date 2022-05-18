<?php

use Illuminate\Support\Facades\Route;

// *************** Staff: CATEGORISATION *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_CATEGORY_MANAGER]], function() {

    Route::get('/staff/categorisation/dashboard', 'Staff\Categorisation\DashboardController@show')->name('staff.categorisation.dashboard');
    Route::get('/staff/categorisation/title-match/category/{category}', 'Staff\Categorisation\DashboardController@categoryTitleMatch')->name('staff.categorisation.title-match.category');
    Route::get('/staff/categorisation/title-match/series/{gameSeries}', 'Staff\Categorisation\DashboardController@seriesTitleMatch')->name('staff.categorisation.title-match.series');
    Route::get('/staff/categorisation/title-match/tag/{tag}', 'Staff\Categorisation\DashboardController@tagTitleMatch')->name('staff.categorisation.title-match.tag');

    // Member suggestions
    Route::get('/staff/categorisation/game-category-suggestions', 'Staff\Categorisation\GameCategorySuggestionController@show')->name('staff.categorisation.game-category-suggestions.index');
    Route::get('/staff/categorisation/game-category-suggestions/approve', 'Staff\Categorisation\GameCategorySuggestionController@approve')->name('staff.categorisation.game-category-suggestions.approve');
    Route::get('/staff/categorisation/game-category-suggestions/approve-all', 'Staff\Categorisation\GameCategorySuggestionController@approveAll')->name('staff.categorisation.game-category-suggestions.approve-all');
    Route::get('/staff/categorisation/game-category-suggestions/deny', 'Staff\Categorisation\GameCategorySuggestionController@deny')->name('staff.categorisation.game-category-suggestions.deny');

    // Categories
    Route::get('/staff/categorisation/category/list', 'Staff\Categorisation\CategoryController@showList')->name('staff.categorisation.category.list');
    Route::match(['get', 'post'], '/staff/categorisation/category/add', 'Staff\Categorisation\CategoryController@addCategory')->name('staff.categorisation.category.add');
    Route::match(['get', 'post'], '/staff/categorisation/category/edit/{categoryId}', 'Staff\Categorisation\CategoryController@editCategory')->name('staff.categorisation.category.edit');
    Route::match(['get', 'post'], '/staff/categorisation/category/delete/{categoryId}', 'Staff\Categorisation\CategoryController@deleteCategory')->name('staff.categorisation.category.delete');

    // Collections
    Route::get('/staff/categorisation/game-collection/list', 'Staff\Categorisation\GameCollectionController@showList')->name('staff.categorisation.game-collection.list');
    Route::match(['get', 'post'], '/staff/categorisation/game-collection/add', 'Staff\Categorisation\GameCollectionController@addCollection')->name('staff.categorisation.game-collection.add');
    Route::match(['get', 'post'], '/staff/categorisation/game-collection/edit/{collectionId}', 'Staff\Categorisation\GameCollectionController@editCollection')->name('staff.categorisation.game-collection.edit');
    Route::match(['get', 'post'], '/staff/categorisation/game-collection/delete/{collectionId}', 'Staff\Categorisation\GameCollectionController@deleteCollection')->name('staff.categorisation.game-collection.delete');

    // Series
    Route::get('/staff/categorisation/game-series/list', 'Staff\Categorisation\GameSeriesController@showList')->name('staff.categorisation.game-series.list');
    Route::match(['get', 'post'], '/staff/categorisation/game-series/add', 'Staff\Categorisation\GameSeriesController@addSeries')->name('staff.categorisation.game-series.add');
    Route::match(['get', 'post'], '/staff/categorisation/game-series/edit/{seriesId}', 'Staff\Categorisation\GameSeriesController@editSeries')->name('staff.categorisation.game-series.edit');
    Route::match(['get', 'post'], '/staff/categorisation/game-series/delete/{seriesId}', 'Staff\Categorisation\GameSeriesController@deleteSeries')->name('staff.categorisation.game-series.delete');

    // Tags
    Route::get('/staff/categorisation/tag/list', 'Staff\Categorisation\TagController@showList')->name('staff.categorisation.tag.list');
    Route::match(['get', 'post'], '/staff/categorisation/tag/add', 'Staff\Categorisation\TagController@addTag')->name('staff.categorisation.tag.add');
    Route::match(['get', 'post'], '/staff/categorisation/tag/edit/{tagId}', 'Staff\Categorisation\TagController@editTag')->name('staff.categorisation.tag.edit');
    Route::get('/staff/categorisation/tag/delete/{tagId}', 'Staff\Categorisation\TagController@deleteTag')->name('staff.categorisation.tag.delete');

    // Migrations
    Route::get('/staff/categorisation/migrations/category/all-games-with-no-category', 'Staff\Categorisation\MigrationsCategoryController@allGamesWithNoCategory')->name('staff.categorisation.migrations.category.all-games-with-no-category');

});
