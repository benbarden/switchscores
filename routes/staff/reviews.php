<?php

use Illuminate\Support\Facades\Route;

// *************** Staff: REVIEWS *************** //
Route::group(['middleware' => ['auth.staff', 'check.user.role:'. \App\Models\UserRole::ROLE_REVIEWS_MANAGER]], function() {

    Route::get('/staff/reviews/dashboard', 'Staff\Reviews\DashboardController@show')->name('staff.reviews.dashboard');

    // Review sites
    Route::get('/staff/reviews/review-sites', 'Staff\Reviews\ReviewSiteController@index')->name('staff.reviews.reviewSites.index');
    Route::match(['get', 'post'], '/staff/reviews/review-sites/add', 'Staff\Reviews\ReviewSiteController@add')->name('staff.reviews.reviewSites.add');
    Route::match(['get', 'post'], '/staff/reviews/review-sites/edit/{reviewSite}', 'Staff\Reviews\ReviewSiteController@edit')->name('staff.reviews.reviewSites.edit');

    // Feed links
    Route::get('/staff/reviews/feed-links', 'Staff\Reviews\FeedLinksController@index')->name('staff.reviews.feedLinks.index');
    Route::match(['get', 'post'], '/staff/reviews/feed-links/add', 'Staff\Reviews\FeedLinksController@add')->name('staff.reviews.feedLinks.add');
    Route::match(['get', 'post'], '/staff/reviews/feed-links/edit/{linkId}', 'Staff\Reviews\FeedLinksController@edit')->name('staff.reviews.feedLinks.edit');

    // Review links
    Route::match(['get', 'post'], '/staff/reviews/link/add', 'Staff\Reviews\ReviewLinkController@add')->name('staff.reviews.link.add');
    Route::match(['get', 'post'], '/staff/reviews/link/edit/{linkId}', 'Staff\Reviews\ReviewLinkController@edit')->name('staff.reviews.link.edit');
    Route::match(['get', 'post'], '/staff/reviews/link/delete/{linkId}', 'Staff\Reviews\ReviewLinkController@delete')->name('staff.reviews.link.delete');
    Route::match(['get', 'post'], '/staff/reviews/link/import', 'Staff\Reviews\ReviewLinkController@import')->name('staff.reviews.link.import');
    Route::get('/staff/reviews/link/{report?}', 'Staff\Reviews\ReviewLinkController@showList')->name('staff.reviews.link.list');

    // Quick reviews
    Route::match(['get', 'post'], '/staff/reviews/quick-reviews/edit/{reviewId}', 'Staff\Reviews\QuickReviewController@edit')->name('staff.reviews.quick-reviews.edit');
    Route::match(['get', 'post'], '/staff/reviews/quick-reviews/delete/{reviewId}', 'Staff\Reviews\QuickReviewController@delete')->name('staff.reviews.quick-reviews.delete');
    Route::get('/staff/reviews/quick-reviews/{report?}', 'Staff\Reviews\QuickReviewController@showList')->name('staff.reviews.quick-reviews.list');

    // Review drafts
    Route::get('/staff/reviews/review-drafts/pending', 'Staff\Reviews\ReviewDraftsController@showPending')->name('staff.reviews.review-drafts.showPending');
    Route::get('/staff/reviews/review-drafts/by-process-status/{status}', 'Staff\Reviews\ReviewDraftsController@byProcessStatus')->name('staff.reviews.review-drafts.by-process-status');
    Route::match(['get', 'post'], '/staff/reviews/review-drafts/edit/{itemId}', 'Staff\Reviews\ReviewDraftsController@edit')->name('staff.reviews.review-drafts.edit');

    // Review campaigns
    Route::get('/staff/reviews/campaigns', 'Staff\Reviews\CampaignsController@show')->name('staff.reviews.campaigns');
    Route::match(['get', 'post'], '/staff/reviews/campaigns/add', 'Staff\Reviews\CampaignsController@add')->name('staff.reviews.campaigns.add');
    Route::match(['get', 'post'], '/staff/reviews/campaigns/edit/{campaignId}', 'Staff\Reviews\CampaignsController@edit')->name('staff.reviews.campaigns.edit');
    Route::match(['get', 'post'], '/staff/reviews/campaigns/edit-games/{campaignId}', 'Staff\Reviews\CampaignsController@editGames')->name('staff.reviews.campaigns.editGames');

    // Unranked lists
    Route::get('/staff/reviews/unranked/review-count', 'Staff\Reviews\UnrankedController@reviewCountLanding')->name('staff.reviews.unranked.review-count-landing');
    Route::get('/staff/reviews/unranked/review-count/{reviewCount}/list', 'Staff\Reviews\UnrankedController@reviewCountList')->name('staff.reviews.unranked.review-count-list');
    Route::get('/staff/reviews/unranked/release-year', 'Staff\Reviews\UnrankedController@releaseYearLanding')->name('staff.reviews.unranked.release-year-landing');
    Route::get('/staff/reviews/unranked/release-year/{releaseYear}/list', 'Staff\Reviews\UnrankedController@releaseYearList')->name('staff.reviews.unranked.release-year-list');

    // Reviews: Tools
    Route::match(['get', 'post'], '/staff/reviews/tools/import-draft-reviews', 'Staff\Reviews\ToolsController@importDraftReviews')->name('staff.reviews.tools.importDraftReviews');
    Route::match(['get', 'post'], '/staff/reviews/tools/review-feed-importer', 'Staff\Reviews\ToolsController@reviewFeedImporter')->name('staff.reviews.tools.reviewFeedImporter');
    Route::match(['get', 'post'], '/staff/reviews/tools/run-feed-importer', 'Staff\Reviews\ToolsController@runFeedImporter')->name('staff.reviews.tools.runFeedImporter');
    Route::match(['get', 'post'], '/staff/reviews/tools/run-feed-parser', 'Staff\Reviews\ToolsController@runFeedParser')->name('staff.reviews.tools.runFeedParser');
    Route::match(['get', 'post'], '/staff/reviews/tools/run-feed-review-generator', 'Staff\Reviews\ToolsController@runFeedReviewGenerator')->name('staff.reviews.tools.runFeedReviewGenerator');
    Route::match(['get', 'post'], '/staff/reviews/tools/update-game-ranks', 'Staff\Reviews\ToolsController@updateGameRanks')->name('staff.reviews.tools.updateGameRanks');
    Route::match(['get', 'post'], '/staff/reviews/tools/update-game-review-stats', 'Staff\Reviews\ToolsController@updateGameReviewStats')->name('staff.reviews.tools.updateGameReviewStats');

});
