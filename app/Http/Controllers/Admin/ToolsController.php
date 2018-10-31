<?php

namespace App\Http\Controllers\Admin;

class ToolsController extends \App\Http\Controllers\BaseController
{
    protected $commandList = [];

    public function __construct()
    {
        $this->commandList = [
            /* *** Wikipedia *** */
            'WikipediaCrawlGamesList' => [
                'command' => 'WikipediaCrawlGamesList',
                'group' => 'Wikipedia',
                'title' => 'Wikipedia Crawl Games List',
                'desc' => 'Crawls the Nintendo Switch games list on Wikipedia and saves data to the WOS database',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0310',
                'nextStep' => 'WikipediaImportGamesList',
            ],
            'WikipediaImportGamesList' => [
                'command' => 'WikipediaImportGamesList',
                'group' => 'Wikipedia',
                'title' => 'Wikipedia Import Games List',
                'desc' => 'Converts crawler data into Feed Items for creating or updating games',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0315',
                'nextStep' => 'WikipediaUpdateGamesList',
                'relatedLink' => [
                    'url' => route('admin.feed-items.games.list'),
                    'text' => 'Feed Items - Games'
                ],
            ],
            'WikipediaUpdateGamesList' => [
                'command' => 'WikipediaUpdateGamesList',
                'group' => 'Wikipedia',
                'title' => 'Wikipedia Update Games List',
                'desc' => 'Processes feed items (games) that are marked as OK to update',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0320',
            ],
            /* *** eShop *** */
            'EshopEuropeImportData' => [
                'command' => 'EshopEuropeImportData',
                'group' => 'Eshop',
                'title' => 'Eshop Europe Import Data',
                'desc' => 'Imports data from the European eShop',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0410',
                'nextStep' => 'EshopEuropeLinkGames',
            ],
            'EshopEuropeLinkGames' => [
                'command' => 'EshopEuropeLinkGames',
                'group' => 'Eshop',
                'title' => 'Eshop Europe Link Games',
                'desc' => 'Attempts to link data from the European eShop to games in the WOS database',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0420',
            ],
            'EshopEuropeUpdateGameData' => [
                'command' => 'EshopEuropeUpdateGameData',
                'group' => 'Eshop',
                'title' => 'Eshop Europe Update Game Data',
                'desc' => 'Updates data for games linked to eShop Europe data records',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0422',
            ],
            'EshopEuropeDownloadPackshots' => [
                'command' => 'EshopEuropeDownloadPackshots',
                'group' => 'Eshop',
                'title' => 'Eshop Europe Download Packshots',
                'desc' => 'Finds packshots from the European eShop, downloads them and links them to games',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0425',
            ],
            /* *** Reviews *** */
            'RunFeedImporter' => [
                'command' => 'RunFeedImporter',
                'group' => 'Reviews',
                'title' => 'Run Feed Importer',
                'desc' => 'Visits RSS feeds from partner sites, and loads any reviews that have not yet been imported',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0510',
                'nextStep' => 'RunFeedParser',
            ],
            'RunFeedParser' => [
                'command' => 'RunFeedParser',
                'group' => 'Reviews',
                'title' => 'Run Feed Parser',
                'desc' => 'Attempts to match titles from the feed importer to games in the WOS database',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0515',
                'nextStep' => 'RunFeedReviewGenerator',
                'relatedLink' => [
                    'url' => route('admin.feed-items.reviews.list'),
                    'text' => 'Feed Items - Reviews'
                ],
            ],
            'RunFeedReviewGenerator' => [
                'command' => 'RunFeedReviewGenerator',
                'group' => 'Reviews',
                'title' => 'Run Feed Review Generator',
                'desc' => 'Creates review links for feed items linked to games and with ratings',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0520',
            ],
            /* *** Games *** */
            'UpdateGameReviewStats' => [
                'command' => 'UpdateGameReviewStats',
                'group' => 'Game updates',
                'title' => 'Update Game Review Stats',
                'desc' => 'Updates the Review Count and Average Score fields for each game',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0525',
            ],
            'UpdateGameRanks' => [
                'command' => 'UpdateGameRanks',
                'group' => 'Game updates',
                'title' => 'Update Game Ranks',
                'desc' => 'Updates the rank field for each game',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0530',
            ],
            'UpdateGameCalendarStats' => [
                'command' => 'UpdateGameCalendarStats',
                'group' => 'Game updates',
                'title' => 'Update Game Calendar Stats',
                'desc' => 'Updates the released game stats on the Release Calendar page',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0535',
            ],
            'UpdateGameImageCount' => [
                'command' => 'UpdateGameImageCount',
                'group' => 'Game updates',
                'title' => 'Update Game Image Count',
                'desc' => 'Updates the Image Count field for each game',
                'scheduleFreq' => 'Manual',
                'scheduleTime' => 'N/A',
            ],
        ];

        parent::__construct();
    }

    private function getToolDetails($commandName)
    {
        if (!array_key_exists($commandName, $this->commandList)) abort(404);

        return $this->commandList[$commandName];
    }

    public function landing()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Tools';
        $bindings['PanelTitle'] = 'Tools';

        $bindings['ToolList'] = $this->commandList;

        return view('admin.tools.landing', $bindings);
    }

    public function toolLandingModular($commandName)
    {
        $bindings = [];

        $toolDetails = $this->getToolDetails($commandName);

        $toolTitle = $toolDetails['title'];

        $bindings['TopTitle'] = 'Tools - '.$toolTitle;
        $bindings['PanelTitle'] = 'Tools - '.$toolTitle;
        $bindings['ToolDetails'] = $toolDetails;

        return view('admin.tools.toolLandingModular', $bindings);
    }

    public function toolProcessModular($commandName)
    {
        $toolDetails = $this->getToolDetails($commandName);

        \Artisan::call($commandName, []);
        $commandOutput = \Artisan::output();

        $bindings = [];

        $toolTitle = $toolDetails['title'];

        $bindings['TopTitle'] = 'Tools - '.$toolTitle;
        $bindings['PanelTitle'] = 'Tools - '.$toolTitle;
        $bindings['ToolDetails'] = $toolDetails;
        $bindings['CommandOutput'] = $commandOutput;

        return view('admin.tools.toolProcessModular', $bindings);
    }

    /* Review importer */

    public function runFeedImporterLanding()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Tools - Run Feed Importer';
        $bindings['PanelTitle'] = 'Tools - Run Feed Importer';

        return view('admin.tools.runFeedImporter.landing', $bindings);
    }

    public function runFeedImporterProcess()
    {
        \Artisan::call('RunFeedImporter');

        $bindings = [];

        $bindings['TopTitle'] = 'Tools - Run Feed Importer';
        $bindings['PanelTitle'] = 'Tools - Run Feed Importer';

        return view('admin.tools.runFeedImporter.process', $bindings);
    }

    public function runFeedParserLanding()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Tools - Run Feed Parser';
        $bindings['PanelTitle'] = 'Tools - Run Feed Parser';

        return view('admin.tools.runFeedParser.landing', $bindings);
    }

    public function runFeedParserProcess()
    {
        \Artisan::call('RunFeedParser');

        $bindings = [];

        $bindings['TopTitle'] = 'Tools - Run Feed Parser';
        $bindings['PanelTitle'] = 'Tools - Run Feed Parser';

        return view('admin.tools.runFeedParser.process', $bindings);
    }

    public function runFeedReviewGeneratorLanding()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Tools - Run Feed Review Generator';
        $bindings['PanelTitle'] = 'Tools - Run Feed Review Generator';

        return view('admin.tools.runFeedReviewGenerator.landing', $bindings);
    }

    public function runFeedReviewGeneratorProcess()
    {
        \Artisan::call('RunFeedReviewGenerator');

        $bindings = [];

        $bindings['TopTitle'] = 'Tools - Run Feed Review Generator';
        $bindings['PanelTitle'] = 'Tools - Run Feed Review Generator';

        return view('admin.tools.runFeedReviewGenerator.process', $bindings);
    }

    /* Wikipedia crawler */

    public function runWikipediaCrawlerLanding()
    {
        $title = 'Tools - Run Wikipedia Crawler';
        $viewName = 'runWikipediaCrawler';

        $bindings = [];

        $bindings['TopTitle'] = $title;
        $bindings['PanelTitle'] = $title;
        $bindings['ViewName'] = $viewName;

        return view('admin.tools.'.$viewName.'.landing', $bindings);
    }

    public function runWikipediaCrawlerProcess()
    {
        \Artisan::call('WikipediaCrawlGamesList');

        $title = 'Tools - Run Wikipedia Crawler';
        $viewName = 'runWikipediaCrawler';

        $bindings = [];

        $bindings['TopTitle'] = $title;
        $bindings['PanelTitle'] = $title;
        $bindings['ViewName'] = $viewName;

        return view('admin.tools.'.$viewName.'.process', $bindings);
    }

    public function runWikipediaImporterLanding()
    {
        $title = 'Tools - Run Wikipedia Importer';
        $viewName = 'runWikipediaImporter';

        $bindings = [];

        $bindings['TopTitle'] = $title;
        $bindings['PanelTitle'] = $title;
        $bindings['ViewName'] = $viewName;

        return view('admin.tools.'.$viewName.'.landing', $bindings);
    }

    public function runWikipediaImporterProcess()
    {
        \Artisan::call('WikipediaImportGamesList');

        $title = 'Tools - Run Wikipedia Importer';
        $viewName = 'runWikipediaImporter';

        $bindings = [];

        $bindings['TopTitle'] = $title;
        $bindings['PanelTitle'] = $title;
        $bindings['ViewName'] = $viewName;

        return view('admin.tools.'.$viewName.'.process', $bindings);
    }

    public function runWikipediaUpdaterLanding()
    {
        $title = 'Tools - Run Wikipedia Updater';
        $viewName = 'runWikipediaUpdater';

        $bindings = [];

        $bindings['TopTitle'] = $title;
        $bindings['PanelTitle'] = $title;
        $bindings['ViewName'] = $viewName;

        return view('admin.tools.'.$viewName.'.landing', $bindings);
    }

    public function runWikipediaUpdaterProcess()
    {
        \Artisan::call('WikipediaUpdateGamesList');

        $title = 'Tools - Run Wikipedia Updater';
        $viewName = 'runWikipediaUpdater';

        $bindings = [];

        $bindings['TopTitle'] = $title;
        $bindings['PanelTitle'] = $title;
        $bindings['ViewName'] = $viewName;

        return view('admin.tools.'.$viewName.'.process', $bindings);
    }

}
