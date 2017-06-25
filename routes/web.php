<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'WelcomeController@show')->name('welcome');

Route::get('/games/released', 'GamesController@listReleased')->name('games.list.released');
Route::get('/games/upcoming', 'GamesController@listUpcoming')->name('games.list.upcoming');
Route::get('/games/top-rated', 'GamesController@listTopRated')->name('games.list.topRated');
Route::get('/games/reviews-needed', 'GamesController@listReviewsNeeded')->name('games.list.reviewsNeeded');

Route::get('/games/{id}', 'GamesController@showId')->name('game.showId');
Route::get('/games/{id}/{title}', 'GamesController@show')->name('game.show');

/* Charts */
Route::get('/charts', 'ChartsController@landing')->name('charts.landing');
Route::get('/charts/most-appearances', 'ChartsController@mostAppearances')->name('charts.mostAppearances');
Route::get('/charts/games-at-position', 'ChartsController@gamesAtPositionLanding')->name('charts.gamesAtPositionLanding');
Route::get('/charts/games-at-position/{position?}', 'ChartsController@gamesAtPosition')->name('charts.gamesAtPosition');

Route::get('/charts/{date?}', 'ChartsController@showEu')->name('charts.date');
Route::get('/charts-us/{date?}', 'ChartsController@showUs')->name('charts.us.date');

/* Blog redirects */
Route::get('/tag/{tag}/', 'BlogController@redirectTag')->name('blog.redirectTag');
Route::get('/category/{tag}/', 'BlogController@redirectCategory')->name('blog.redirectCategory');
Route::get('/{year}/{month}/{day}/{title}/', 'BlogController@redirectPost')->name('blog.redirectPost');

/* Misc redirects */
Route::get('/lists/released-nintendo-switch-games', 'ListsController@releasedGames')->name('lists.released');
Route::get('/lists/upcoming-nintendo-switch-games', 'ListsController@upcomingGames')->name('lists.upcoming');
