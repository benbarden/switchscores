<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

// Request invite code
Route::match(['get', 'post'], '/request-invite-code', 'Auth\RegisterController@requestInviteCode')->name('auth.register.request-invite-code');
Route::get('/invite-request-success', 'PublicSite\AboutController@inviteRequestSuccess')->name('about.invite-request-success');
Route::get('/invite-request-failure', 'PublicSite\AboutController@inviteRequestFailure')->name('about.invite-request-failure');

// Third-party logins
Route::get('/login/twitter', 'Auth\LoginController@redirectToProviderTwitter')->name('auth.login.twitter');
Route::get('/login/twitter/callback', 'Auth\LoginController@handleProviderCallbackTwitter')->name('auth.login.twitter.callback');

