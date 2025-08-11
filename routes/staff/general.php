<?php

use Illuminate\Support\Facades\Route;

// *************** Staff: General pages *************** //
Route::group(['middleware' => ['auth.staff']], function() {

    Route::get('/staff', 'Staff\IndexController@index')->name('staff.index');

});

// *************** Staff: Tools *************** //
Route::prefix('staff')->middleware(['auth.staff'])->name('staff.')->group(function () {
    Route::get('/tools', 'Staff\ToolsController@index')->name('tools.index');
    Route::post('/tools/run/{cmd}', 'Staff\ToolsController@run')->name('tools.run');
    Route::post('/tools/run-group/{groupKey}', 'Staff\ToolsController@runGroup')->name('tools.runGroup');
});

// *************** Staff: INVITE CODES *************** //
Route::group(['middleware' => ['auth.staff']], function() {

    Route::get('/staff/invite-code-request/list', 'Staff\InviteCodeRequestController@showList')->name('staff.invite-code-request.list');

    Route::get('/staff/invite-code/list', 'Staff\InviteCodeController@showList')->name('staff.invite-code.list');
    Route::match(['get', 'post'], '/staff/invite-code/add', 'Staff\InviteCodeController@addInviteCode')->name('staff.invite-code.add');
    Route::match(['get', 'post'], '/staff/invite-code/edit/{inviteCodeId}', 'Staff\InviteCodeController@editInviteCode')->name('staff.invite-code.edit');
    Route::match(['get', 'post'], '/staff/invite-code/delete/{inviteCodeId}', 'Staff\InviteCodeController@deleteInviteCode')->name('staff.invite-code.delete');
    Route::match(['get', 'post'], '/staff/invite-code/generate-codes', 'Staff\InviteCodeController@generateCodes')->name('staff.invite-code.generate-codes');
});
