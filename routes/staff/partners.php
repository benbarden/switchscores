<?php

use Illuminate\Support\Facades\Route;

// *************** Staff: PARTNERS *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_PARTNERSHIPS_MANAGER]], function() {

    Route::get('/staff/partners/dashboard', 'Staff\Partners\DashboardController@show')->name('staff.partners.dashboard');

    /*
        // Partners: Review sites
        Route::get('/staff/partners/review-sites', 'Staff\Partners\ReviewSiteController@showList')->name('staff.reviews.site.list');
        Route::match(['get', 'post'], '/staff/partners/review-sites/add', 'Staff\Partners\ReviewSiteController@add')->name('staff.reviews.site.add');
        Route::match(['get', 'post'], '/staff/partners/review-sites/edit/{siteId}', 'Staff\Partners\ReviewSiteController@edit')->name('staff.reviews.site.edit');

        // Partners: Feed links
        Route::get('/staff/partners/feed-links', 'Staff\Partners\FeedLinksController@showList')->name('staff.partners.feed-links.list');
        Route::match(['get', 'post'], '/staff/partners/feed-links/add', 'Staff\Partners\FeedLinksController@add')->name('staff.partners.feed-links.add');
        Route::match(['get', 'post'], '/staff/partners/feed-links/edit/{linkId}', 'Staff\Partners\FeedLinksController@edit')->name('staff.partners.feed-links.edit');
    */

    // Partners: Games companies
    Route::match(['get', 'post'], '/staff/partners/games-company/add', 'Staff\Partners\GamesCompanyController@add')->name('staff.partners.games-company.add');
    Route::match(['get', 'post'], '/staff/partners/games-company/edit/{gamesCompanyId}', 'Staff\Partners\GamesCompanyController@edit')->name('staff.partners.games-company.edit');
    Route::match(['get', 'post'], '/staff/partners/games-company/delete/{gamesCompanyId}', 'Staff\Partners\GamesCompanyController@delete')->name('staff.partners.games-company.delete');

    Route::get('/staff/partners/games-company/show/{gamesCompany}', 'Staff\Partners\GamesCompanyController@show')->name('staff.partners.games-company.show');

    Route::get('/staff/partners/games-company/list', 'Staff\Partners\GamesCompanyController@showList')->name('staff.partners.games-company.list');

    Route::get('/staff/partners/games-company/normal-quality', 'Staff\Partners\GamesCompanyController@normalQuality')->name('staff.partners.games-company.normal-quality');
    Route::get('/staff/partners/games-company/low-quality', 'Staff\Partners\GamesCompanyController@lowQuality')->name('staff.partners.games-company.low-quality');

    Route::get('/staff/partners/games-company/devs-with-unranked-games', 'Staff\Partners\GamesCompanyController@devsWithUnrankedGames')->name('staff.partners.games-company.devs-with-unranked-games');
    Route::get('/staff/partners/games-company/pubs-with-unranked-games', 'Staff\Partners\GamesCompanyController@pubsWithUnrankedGames')->name('staff.partners.games-company.pubs-with-unranked-games');

    Route::get('/staff/partners/games-company/without-twitter-ids', 'Staff\Partners\GamesCompanyController@withoutTwitterIds')->name('staff.partners.games-company.without-twitter-ids');
    Route::get('/staff/partners/games-company/without-website-urls', 'Staff\Partners\GamesCompanyController@withoutWebsiteUrls')->name('staff.partners.games-company.without-website-urls');
    Route::get('/staff/partners/games-company/duplicate-twitter-ids', 'Staff\Partners\GamesCompanyController@duplicateTwitterIds')->name('staff.partners.games-company.duplicate-twitter-ids');
    Route::get('/staff/partners/games-company/duplicate-website-urls', 'Staff\Partners\GamesCompanyController@duplicateWebsiteUrls')->name('staff.partners.games-company.duplicate-website-urls');

    // Partners: Outreach
    Route::get('/staff/partners/outreach/list/{gamesCompany?}', 'Staff\Partners\OutreachController@showList')->name('staff.partners.outreach.list');
    Route::match(['get', 'post'], '/staff/partners/outreach/add', 'Staff\Partners\OutreachController@add')->name('staff.partners.outreach.add');
    Route::match(['get', 'post'], '/staff/partners/outreach/edit/{partnerOutreach}', 'Staff\Partners\OutreachController@edit')->name('staff.partners.outreach.edit');

    // Partners: Data cleanup
    Route::get('/staff/partners/data-cleanup/games-with-missing-developer', 'Staff\Partners\DataCleanupController@gamesWithMissingDeveloper')->name('staff.partners.data-cleanup.games-with-missing-developer');
    Route::get('/staff/partners/data-cleanup/games-with-missing-publisher', 'Staff\Partners\DataCleanupController@gamesWithMissingPublisher')->name('staff.partners.data-cleanup.games-with-missing-publisher');

    // Partners: Tools
    Route::match(['get', 'post'], '/staff/partners/tools/partner-update-fields', 'Staff\Partners\ToolsController@partnerUpdateFields')->name('staff.partners.tools.partnerUpdateFields');

});
