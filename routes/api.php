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

Route::group(['middleware' => ['auth:sanctum']], function() {

    Route::get('/v2/games/list-all', 'Api\V2\GameController@getList')->name('api.v2.game.list');

    Route::get('/v2/game/{id}', 'Api\V2\GameController@getGameDetails')->name('api.v2.game.get');

});

// Game
Route::get('/game/get-by-exact-title-match', 'Api\Game\TitleMatch@getByExactTitleMatch');
Route::get('/game/get-unlinked-data-source-item', 'Api\Game\TitleMatch@getUnlinkedDataSourceItem');

Route::get('/game/find-by-title', 'Api\Game\GameController@findByTitle');

// Partner
Route::get('/partner/games-company/search', 'Api\Partner\GamesCompanyController@findByName');

/* LP */
// Game ids
Route::get('/game/list', 'Api\Game\GameController@getList');
// Game details
Route::get('/game/{id}', 'Api\Game\GameController@getDetails');
// Game details from linkid
Route::get('/game/linkid/{id}', 'Api\Game\GameController@getDetailsByLinkId');

// Game reviews
Route::get('/game/{id}/reviews', 'Api\Game\GameController@getReviews');

/* Admin */
Route::get('/review/site', 'Api\ReviewSiteController@getByDomain');
Route::get('/url/link-text', 'Api\UrlController@generateLinkText');
Route::get('/url/news-url', 'Api\UrlController@generateNewsUrl');

// Games manager
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_GAMES_MANAGER]], function() {
    Route::post('/game/bulk-update', 'Api\Game\BulkUpdate@eshopUpcomingCrosscheck')->name('api.game.bulk-update.eshop-upcoming-crosscheck');
});


/* Admin-restricted */
Route::group(['middleware' => ['auth.admin:admin']], function() {

    // Sanity checking for admin-restricted API routes
    Route::get('/admin/auth-test', 'Api\Admin\AuthTest@quickCheck');

    Route::get('/admin/developer/add-game-developer', 'Api\Admin\Developer@addGameDeveloper');

});

/* Staff-restricted */
// WIKIPEDIA
/*
Route::group(['middleware' => ['auth.staff', 'check.user.role:'.\App\Models\UserRole::ROLE_WIKIPEDIA_MANAGER]], function() {

    Route::get('/staff/wikipedia/wiki-updates/update-status', 'Api\Staff\WikiUpdate@updateStatus');

});
*/
