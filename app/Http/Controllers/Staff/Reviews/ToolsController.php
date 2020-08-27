<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class ToolsController extends Controller
{
    use SwitchServices;

    public function runFeedImporter()
    {
        $pageTitle = 'Run Feed Importer';
        $topTitle = $pageTitle.' - Tools - Reviews - Staff';

        $bindings = [];
        $bindings['TopTitle'] = $topTitle;
        $bindings['PageTitle'] = $pageTitle;

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
        $topTitle = $pageTitle.' - Tools - Reviews - Staff';

        $bindings = [];
        $bindings['TopTitle'] = $topTitle;
        $bindings['PageTitle'] = $pageTitle;

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
        $topTitle = $pageTitle.' - Tools - Reviews - Staff';

        $bindings = [];
        $bindings['TopTitle'] = $topTitle;
        $bindings['PageTitle'] = $pageTitle;

        if (request()->post()) {
            \Artisan::call('RunFeedReviewGenerator', []);
            return view('staff.reviews.tools.runFeedReviewGenerator.process', $bindings);
        } else {
            return view('staff.reviews.tools.runFeedReviewGenerator.landing', $bindings);
        }
    }

    public function updateGameRanks()
    {
        $pageTitle = 'Update Game Ranks';
        $topTitle = $pageTitle.' - Tools - Reviews - Staff';

        $bindings = [];
        $bindings['TopTitle'] = $topTitle;
        $bindings['PageTitle'] = $pageTitle;

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
        $topTitle = $pageTitle.' - Tools - Reviews - Staff';

        $bindings = [];
        $bindings['TopTitle'] = $topTitle;
        $bindings['PageTitle'] = $pageTitle;

        if (request()->post()) {
            \Artisan::call('UpdateGameReviewStats', []);
            return view('staff.reviews.tools.updateGameReviewStats.process', $bindings);
        } else {
            return view('staff.reviews.tools.updateGameReviewStats.landing', $bindings);
        }
    }
}
