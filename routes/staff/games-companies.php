<?php

use Illuminate\Support\Facades\Route;

// *************** Staff: GAMES COMPANIES *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_PARTNERSHIPS_MANAGER]], function() {

    Route::get('/staff/games-companies/dashboard', 'Staff\GamesCompanies\DashboardController@show')->name('staff.games-companies.dashboard');

    // Partners: Games companies
    Route::match(['get', 'post'], '/staff/games-companies/add', 'Staff\GamesCompanies\CompanyController@add')->name('staff.games-companies.add');
    Route::match(['get', 'post'], '/staff/games-companies/edit/{gamesCompanyId}', 'Staff\GamesCompanies\CompanyController@edit')->name('staff.games-companies.edit');
    Route::match(['get', 'post'], '/staff/games-companies/delete/{gamesCompanyId}', 'Staff\GamesCompanies\CompanyController@delete')->name('staff.games-companies.delete');

    Route::get('/staff/games-companies/show/{gamesCompany}', 'Staff\GamesCompanies\CompanyController@show')->name('staff.games-companies.show');

    Route::get('/staff/games-companies/list', 'Staff\GamesCompanies\ListController@showList')->name('staff.games-companies.list');

    Route::get('/staff/games-companies/normal-quality', 'Staff\GamesCompanies\ListController@normalQuality')->name('staff.games-companies.normal-quality');
    Route::get('/staff/games-companies/low-quality', 'Staff\GamesCompanies\ListController@lowQuality')->name('staff.games-companies.low-quality');

    Route::get('/staff/games-companies/pubs-with-unranked-games/{releaseYear?}', 'Staff\GamesCompanies\ListController@pubsWithUnrankedGames')->name('staff.games-companies.pubs-with-unranked-games');

    Route::get('/staff/games-companies/without-twitter-ids', 'Staff\GamesCompanies\ListController@withoutTwitterIds')->name('staff.games-companies.without-twitter-ids');
    Route::get('/staff/games-companies/without-website-urls', 'Staff\GamesCompanies\ListController@withoutWebsiteUrls')->name('staff.games-companies.without-website-urls');
    Route::get('/staff/games-companies/duplicate-twitter-ids', 'Staff\GamesCompanies\ListController@duplicateTwitterIds')->name('staff.games-companies.duplicate-twitter-ids');
    Route::get('/staff/games-companies/duplicate-website-urls', 'Staff\GamesCompanies\ListController@duplicateWebsiteUrls')->name('staff.games-companies.duplicate-website-urls');

    // Partners: Outreach
    Route::get('/staff/partners/outreach/list/{gamesCompany?}', 'Staff\Partners\OutreachController@showList')->name('staff.partners.outreach.list');
    Route::match(['get', 'post'], '/staff/partners/outreach/add/{gamesCompany?}', 'Staff\Partners\OutreachController@add')->name('staff.partners.outreach.add');
    Route::match(['get', 'post'], '/staff/partners/outreach/edit/{partnerOutreach}', 'Staff\Partners\OutreachController@edit')->name('staff.partners.outreach.edit');

    // Partners: Data cleanup
    Route::get('/staff/partners/data-cleanup/games-with-missing-developer', 'Staff\Partners\DataCleanupController@gamesWithMissingDeveloper')->name('staff.partners.data-cleanup.games-with-missing-developer');
    Route::get('/staff/partners/data-cleanup/games-with-missing-publisher', 'Staff\Partners\DataCleanupController@gamesWithMissingPublisher')->name('staff.partners.data-cleanup.games-with-missing-publisher');

    // Partners: Tools
    Route::match(['get', 'post'], '/staff/partners/tools/partner-update-fields', 'Staff\Partners\ToolsController@partnerUpdateFields')->name('staff.partners.tools.partnerUpdateFields');

});
