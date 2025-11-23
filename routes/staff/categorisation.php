<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Staff\Categorisation\BulkToolsController;

// *************** Staff: CATEGORISATION *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_CATEGORY_MANAGER]], function() {

    Route::get('/staff/categorisation/dashboard', 'Staff\Categorisation\DashboardController@show')->name('staff.categorisation.dashboard');

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

    // Bulk tools
    Route::prefix('staff/categorisation/bulk-tools')->group(function () {

        // 1. Bulk move from category A → category B
        Route::get('/move-category-to-category',
            [BulkToolsController::class, 'moveCategoryToCategory'])
            ->name('staff.categorisation.bulk-tools.move-category-to-category');

        Route::post('/move-category-to-category/preview',
            [BulkToolsController::class, 'moveCategoryToCategoryPreview'])
            ->name('staff.categorisation.bulk-tools.move-category-to-category.preview');

        Route::post('/move-category-to-category/run',
            [BulkToolsController::class, 'moveCategoryToCategoryRun'])
            ->name('staff.categorisation.bulk-tools.move-category-to-category.run');

        // 2. Bulk add tag to all games in category A
        Route::get('/add-tag-to-games-in-category',
            [BulkToolsController::class, 'addTagToGamesInCategory'])
            ->name('staff.categorisation.bulk-tools.add-tag-to-games-in-category');

        Route::post('/add-tag-to-games-in-category/preview',
            [BulkToolsController::class, 'addTagToGamesInCategoryPreview'])
            ->name('staff.categorisation.bulk-tools.add-tag-to-games-in-category.preview');

        Route::post('/add-tag-to-games-in-category/run',
            [BulkToolsController::class, 'addTagToGamesInCategoryRun'])
            ->name('staff.categorisation.bulk-tools.add-tag-to-games-in-category.run');

        // 3. Bulk move games with tag X → category B
        Route::get('/move-games-with-tag-to-category',
            [BulkToolsController::class, 'moveGamesWithTagToCategory'])
            ->name('staff.categorisation.bulk-tools.move-games-with-tag-to-category');

        Route::post('/move-games-with-tag-to-category/preview',
            [BulkToolsController::class, 'moveGamesWithTagToCategoryPreview'])
            ->name('staff.categorisation.bulk-tools.move-games-with-tag-to-category.preview');

        Route::post('/move-games-with-tag-to-category/run',
            [BulkToolsController::class, 'moveGamesWithTagToCategoryRun'])
            ->name('staff.categorisation.bulk-tools.move-games-with-tag-to-category.run');

        // 4. Bulk untag all games with tag X
        Route::get('/untag-games-with-tag',
            [BulkToolsController::class, 'untagGamesWithTag'])
            ->name('staff.categorisation.bulk-tools.untag-games-with-tag');

        Route::post('/untag-games-with-tag/preview',
            [BulkToolsController::class, 'untagGamesWithTagPreview'])
            ->name('staff.categorisation.bulk-tools.untag-games-with-tag.preview');

        Route::post('/untag-games-with-tag/run',
            [BulkToolsController::class, 'untagGamesWithTagRun'])
            ->name('staff.categorisation.bulk-tools.untag-games-with-tag.run');

        // 5. Bulk untag all games with category X and tag Y
        Route::get('/untag-games-with-category-and-tag',
            [BulkToolsController::class, 'untagGamesWithCategoryAndTag'])
            ->name('staff.categorisation.bulk-tools.untag-games-with-category-and-tag');

        Route::post('/untag-games-with-category-and-tag/preview',
            [BulkToolsController::class, 'untagGamesWithCategoryAndTagPreview'])
            ->name('staff.categorisation.bulk-tools.untag-games-with-category-and-tag.preview');

        Route::post('/untag-games-with-category-and-tag/run',
            [BulkToolsController::class, 'untagGamesWithCategoryAndTagRun'])
            ->name('staff.categorisation.bulk-tools.untag-games-with-category-and-tag.run');

    });
});
