<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/* Game */
Route::get('/game/title-match', 'Api\Game\TitleMatch@getByTitle');

/* Admin */
Route::get('/review/site', 'Api\ReviewSiteController@getByDomain');
Route::get('/url/link-text', 'Api\UrlController@generateLinkText');
Route::get('/url/news-url', 'Api\UrlController@generateNewsUrl');

/* Admin-restricted */
Route::group(['middleware' => ['auth.admin:admin']], function() {

    // Sanity checking for admin-restricted API routes
    Route::get('/admin/auth-test', 'Api\Admin\AuthTest@quickCheck');

    Route::get('/admin/developer/add-game-developer', 'Api\Admin\Developer@addGameDeveloper');

});

/* Staff-restricted */
// WIKIPEDIA
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\UserRole::ROLE_WIKIPEDIA_MANAGER]], function() {

    Route::get('/staff/wikipedia/wiki-updates/update-status', 'Api\Staff\WikiUpdate@updateStatus');

});
