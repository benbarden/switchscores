<?php

use Illuminate\Support\Facades\Route;

// *************** Reviewers *************** //
Route::prefix('reviewers')->middleware(['auth.reviewer'])->name('reviewers.')->group(function () {

    // *************** Reviewers: Dashboard *************** //
    Route::get('/', 'Members\Reviewers\IndexController@show')->name('index');

    // *************** Reviewers: Edit details *************** //
    Route::match(['get', 'post'], '/edit-details', 'Members\Reviewers\ProfileController@edit')->name('profile.edit');

    // *************** Reviewers: Manually add reviews *************** //
    Route::match(['get', 'post'], '/reviews/review-draft/find-game', 'Members\Reviewers\ReviewDraftController@findGame')->name('review-draft.find-game');
    Route::match(['get', 'post'], '/reviews/review-draft/add/{gameId}', 'Members\Reviewers\ReviewDraftController@add')->name('review-draft.add');
    Route::match(['get', 'post'], '/reviews/review-draft/edit/{reviewDraft}', 'Members\Reviewers\ReviewDraftController@edit')->name('review-draft.edit');

    // *************** Reviewers: Import reviews *************** //
    Route::match(['get', 'post'], '/tools/import-reviews', 'Members\Reviewers\ToolsController@importReviews')->name('tools.import-reviews');

    // *************** Reviewers: Campaigns *************** //
    Route::get('/campaigns/{campaignId}', 'Members\Reviewers\CampaignsController@show')->name('campaigns.show');

    // *************** Reviewers: Games *************** //
    Route::get('/games/{gameId}', 'Members\Reviewers\GamesController@show')->name('games.show');

    // *************** Reviewers: Stats *************** //
    Route::get('/stats', 'Members\Reviewers\StatsController@landing')->name('stats.landing');

    // *************** Reviewers: Feed health *************** //
    Route::get('/feed-health', 'Members\Reviewers\FeedHealthController@landing')->name('feed-health.landing');
    Route::get('/feed-health/by-process-status/{status}', 'Members\Reviewers\FeedHealthController@byProcessStatus')->name('feed-health.by-process-status');
    Route::get('/feed-health/by-parse-status/{status}', 'Members\Reviewers\FeedHealthController@byParseStatus')->name('feed-health.by-parse-status');

    // *************** Reviewers: Review links *************** //
    Route::get('/reviews/{report?}', 'Members\Reviewers\ReviewLinkController@landing')->name('reviews.landing');

    // *************** Reviewers: Unranked games *************** //
    Route::get('/unranked-games/{mode}/{filter}', 'Members\Reviewers\UnrankedGamesController@showList')->name('unranked-games.list');

});
