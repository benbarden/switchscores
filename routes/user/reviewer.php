<?php

use Illuminate\Support\Facades\Route;

// *************** Reviewers *************** //
Route::group(['middleware' => ['auth.reviewer']], function() {

    // *************** Reviewers: Dashboard *************** //
    Route::get('/reviewers', 'Reviewers\IndexController@show')->name('reviewers.index');

    // *************** Reviewers: Edit details *************** //
    Route::match(['get', 'post'], '/reviewers/edit-details', 'Reviewers\ProfileController@edit')->name('reviewers.profile.edit');

    // *************** Reviewers: Manually add reviews *************** //
    Route::match(['get', 'post'], '/reviewers/reviews/review-draft/find-game', 'Reviewers\ReviewDraftController@findGame')->name('reviewers.review-draft.find-game');
    Route::match(['get', 'post'], '/reviewers/reviews/review-draft/add/{gameId}', 'Reviewers\ReviewDraftController@add')->name('reviewers.review-draft.add');
    Route::match(['get', 'post'], '/reviewers/reviews/review-draft/edit/{reviewDraft}', 'Reviewers\ReviewDraftController@edit')->name('reviewers.review-draft.edit');

    // *************** Reviewers: Import reviews *************** //
    Route::match(['get', 'post'], '/reviewers/tools/import-reviews', 'Reviewers\ToolsController@importReviews')->name('reviewers.tools.import-reviews');

    // *************** Reviewers: Campaigns *************** //
    Route::get('/reviewers/campaigns/{campaignId}', 'Reviewers\CampaignsController@show')->name('reviewers.campaigns.show');

    // *************** Reviewers: Games *************** //
    Route::get('/reviewers/games/{gameId}', 'Reviewers\GamesController@show')->name('reviewers.games.show');

    // *************** Reviewers: Stats *************** //
    Route::get('/reviewers/stats', 'Reviewers\StatsController@landing')->name('reviewers.stats.landing');

    // *************** Reviewers: Feed health *************** //
    Route::get('/reviewers/feed-health', 'Reviewers\FeedHealthController@landing')->name('reviewers.feed-health.landing');
    Route::get('/reviewers/feed-health/by-process-status/{status}', 'Reviewers\FeedHealthController@byProcessStatus')->name('reviewers.feed-health.by-process-status');
    Route::get('/reviewers/feed-health/by-parse-status/{status}', 'Reviewers\FeedHealthController@byParseStatus')->name('reviewers.feed-health.by-parse-status');

    // *************** Reviewers: Review links *************** //
    Route::get('/reviewers/reviews/{report?}', 'Reviewers\ReviewLinkController@landing')->name('reviewers.reviews.landing');

    // *************** Reviewers: Unranked games *************** //
    Route::get('/reviewers/unranked-games/{mode}/{filter}', 'Reviewers\UnrankedGamesController@showList')->name('reviewers.unranked-games.list');

});
