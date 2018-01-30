<?php

namespace App\Http\Controllers\Admin;

class ToolsController extends \App\Http\Controllers\BaseController
{
    public function landing()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Tools';
        $bindings['PanelTitle'] = 'Tools';

        return view('admin.tools.landing', $bindings);
    }

    /* Review importer */

    public function runFeedImporterLanding()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Run Feed Importer';
        $bindings['PanelTitle'] = 'Tools - Run Feed Importer';

        return view('admin.tools.runFeedImporter.landing', $bindings);
    }

    public function runFeedImporterProcess()
    {
        \Artisan::call('RunFeedImporter');

        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Run Feed Importer';
        $bindings['PanelTitle'] = 'Tools - Run Feed Importer';

        return view('admin.tools.runFeedImporter.process', $bindings);
    }

    public function runFeedParserLanding()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Run Feed Parser';
        $bindings['PanelTitle'] = 'Tools - Run Feed Parser';

        return view('admin.tools.runFeedParser.landing', $bindings);
    }

    public function runFeedParserProcess()
    {
        \Artisan::call('RunFeedParser');

        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Run Feed Parser';
        $bindings['PanelTitle'] = 'Tools - Run Feed Parser';

        return view('admin.tools.runFeedParser.process', $bindings);
    }

    public function runFeedReviewGeneratorLanding()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Run Feed Review Generator';
        $bindings['PanelTitle'] = 'Tools - Run Feed Review Generator';

        return view('admin.tools.runFeedReviewGenerator.landing', $bindings);
    }

    public function runFeedReviewGeneratorProcess()
    {
        \Artisan::call('RunFeedReviewGenerator');

        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Run Feed Review Generator';
        $bindings['PanelTitle'] = 'Tools - Run Feed Review Generator';

        return view('admin.tools.runFeedReviewGenerator.process', $bindings);
    }

    /* Game updates */

    public function updateGameRanksLanding()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Update Game Ranks';
        $bindings['PanelTitle'] = 'Tools - Update Game Ranks';

        return view('admin.tools.updateGameRanks.landing', $bindings);
    }

    public function updateGameRanksProcess()
    {
        \Artisan::call('UpdateGameRanks');

        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Update Game Ranks';
        $bindings['PanelTitle'] = 'Tools - Update Game Ranks';

        return view('admin.tools.updateGameRanks.process', $bindings);
    }

    public function updateGameImageCountLanding()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Update Game Image Count';
        $bindings['PanelTitle'] = 'Tools - Update Game Image Count';

        return view('admin.tools.updateGameImageCount.landing', $bindings);
    }

    public function updateGameImageCountProcess()
    {
        \Artisan::call('UpdateGameImageCount');

        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Update Game Image Count';
        $bindings['PanelTitle'] = 'Tools - Update Game Image Count';

        return view('admin.tools.updateGameImageCount.process', $bindings);
    }

    public function updateGameReviewStatsLanding()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Update Game Review Stats';
        $bindings['PanelTitle'] = 'Tools - Update Game Review Stats';

        return view('admin.tools.updateGameReviewStats.landing', $bindings);
    }

    public function updateGameReviewStatsProcess()
    {
        \Artisan::call('UpdateGameReviewStats');

        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Update Game Review Stats';
        $bindings['PanelTitle'] = 'Tools - Update Game Review Stats';

        return view('admin.tools.updateGameReviewStats.process', $bindings);
    }
}
