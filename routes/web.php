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

Route::get('/games/{id}', 'GamesController@show')->name('game.show');

Route::get('/charts/{date?}', 'ChartsController@show')->name('charts.date');

Route::get('/lists/released-nintendo-switch-games', 'ListsController@releasedGames')->name('lists.released');
Route::get('/lists/upcoming-nintendo-switch-games', 'ListsController@upcomingGames')->name('lists.upcoming');
