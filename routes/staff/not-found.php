<?php

use Illuminate\Support\Facades\Route;

/*
 * Any /staff URL no other staff route matches. Loaded after every other staff route file
 * (see RouteServiceProvider::mapStaffRoutes), so it only catches what they don't.
 *
 * Without it, an unknown staff URL never reaches the web middleware, so the error page has no
 * session and can't tell a staff user is logged in. Through here, the 404 is rendered with the
 * staff layout (App\Exceptions\Handler::renderHttpException).
 */
Route::prefix('staff')->middleware(['auth.staff'])->group(function () {
    Route::get('{any}', function () {
        abort(404);
    })->where('any', '.*')->name('staff.not-found');
});
