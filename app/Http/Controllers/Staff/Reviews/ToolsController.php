<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ToolsController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function importDraftReviews()
    {
        $bindings = $this->getBindingsReviewsSubpage('Import draft reviews');

        if (request()->post()) {
            \Artisan::call('ReviewConvertDraftsToReviews', []);
            \Artisan::call('PartnerUpdateFields', []);
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
        $bindings = $this->getBindingsReviewsSubpage('Run Feed Importer');

        if (request()->post()) {
            \Artisan::call('RunFeedImporter', []);
            return view('staff.reviews.tools.runFeedImporter.process', $bindings);
        } else {
            return view('staff.reviews.tools.runFeedImporter.landing', $bindings);
        }
    }

    public function runFeedParser()
    {
        $bindings = $this->getBindingsReviewsSubpage('Run Feed Parser');

        if (request()->post()) {
            \Artisan::call('RunFeedParser', []);
            return view('staff.reviews.tools.runFeedParser.process', $bindings);
        } else {
            return view('staff.reviews.tools.runFeedParser.landing', $bindings);
        }
    }

    public function runFeedReviewGenerator()
    {
        $bindings = $this->getBindingsReviewsSubpage('Run Feed Review Generator');

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
        $bindings = $this->getBindingsReviewsSubpage('Update Game Ranks');

        if (request()->post()) {
            \Artisan::call('UpdateGameRanks', []);
            return view('staff.reviews.tools.updateGameRanks.process', $bindings);
        } else {
            return view('staff.reviews.tools.updateGameRanks.landing', $bindings);
        }
    }

    public function updateGameReviewStats()
    {
        $bindings = $this->getBindingsReviewsSubpage('Update Game Review Stats');

        if (request()->post()) {
            \Artisan::call('UpdateGameReviewStats', []);
            return view('staff.reviews.tools.updateGameReviewStats.process', $bindings);
        } else {
            return view('staff.reviews.tools.updateGameReviewStats.landing', $bindings);
        }
    }
}
