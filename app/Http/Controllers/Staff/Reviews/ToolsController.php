<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

class ToolsController extends Controller
{
    public function importDraftReviews()
    {
        $pageTitle = 'Import draft reviews';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('ReviewConvertDraftsToReviews', []);
            \Artisan::call('ReviewSiteUpdateStats', []);
            \Artisan::call('UpdateGameRanks', []);
            \Artisan::call('ReviewCampaignUpdateProgress', []);
            \Artisan::call('UpdateGameReviewStats', []);
            return view('staff.reviews.tools.importDraftReviews.process', $bindings);
        } else {
            return view('staff.reviews.tools.importDraftReviews.landing', $bindings);
        }
    }

    public function runFeedImporter()
    {
        $pageTitle = 'Run Feed Importer';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('RunFeedImporter', []);
            return view('staff.reviews.tools.runFeedImporter.process', $bindings);
        } else {
            return view('staff.reviews.tools.runFeedImporter.landing', $bindings);
        }
    }

    public function runFeedParser()
    {
        $pageTitle = 'Run Feed Parser';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('RunFeedParser', []);
            return view('staff.reviews.tools.runFeedParser.process', $bindings);
        } else {
            return view('staff.reviews.tools.runFeedParser.landing', $bindings);
        }
    }

    public function runFeedReviewGenerator()
    {
        $pageTitle = 'Run Feed Review Generator';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('RunFeedReviewGenerator', []);
            \Artisan::call('UpdateGameRanks', []);
            return view('staff.reviews.tools.runFeedReviewGenerator.process', $bindings);
        } else {
            return view('staff.reviews.tools.runFeedReviewGenerator.landing', $bindings);
        }
    }

    public function updateGameRanks()
    {
        $pageTitle = 'Update Game Ranks';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('UpdateGameRanks', []);
            return view('staff.reviews.tools.updateGameRanks.process', $bindings);
        } else {
            return view('staff.reviews.tools.updateGameRanks.landing', $bindings);
        }
    }

    public function updateGameReviewStats()
    {
        $pageTitle = 'Update Game Review Stats';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('UpdateGameReviewStats', []);
            return view('staff.reviews.tools.updateGameReviewStats.process', $bindings);
        } else {
            return view('staff.reviews.tools.updateGameReviewStats.landing', $bindings);
        }
    }
}
