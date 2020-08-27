<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

class ToolsController extends Controller
{
    protected $commandList = [];

    public function __construct()
    {
        $this->commandList = [
            'UpdateGameCalendarStats' => [
                'command' => 'UpdateGameCalendarStats',
                'group' => 'Game updates',
                'title' => 'Update Game Calendar Stats',
                'desc' => 'Updates the released game stats on the Release Calendar page',
                'scheduleFreq' => 'Daily',
                'scheduleTime' => '0540',
            ],
        ];

        //parent::__construct();
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
        $bindings['PageTitle'] = 'Tools';

        $bindings['ToolList'] = $this->commandList;

        return view('admin.tools.landing', $bindings);
    }

    public function toolLandingModular($commandName)
    {
        $bindings = [];

        $toolDetails = $this->getToolDetails($commandName);

        $toolTitle = $toolDetails['title'];

        $bindings['TopTitle'] = 'Tools - '.$toolTitle;
        $bindings['PageTitle'] = $toolTitle;
        $bindings['ToolDetails'] = $toolDetails;

        return view('admin.tools.toolLandingModular', $bindings);
    }

    public function toolProcessModular($commandName)
    {
        $toolDetails = $this->getToolDetails($commandName);

        \Artisan::call($commandName, []);
        $commandOutput = \Artisan::output();

        if ($commandName == 'RunFeedReviewGenerator') {
            // Also run the PartnerUpdateFields command
            \Artisan::call('PartnerUpdateFields', []);
        }

        $bindings = [];

        $toolTitle = $toolDetails['title'];

        $bindings['TopTitle'] = 'Tools - '.$toolTitle;
        $bindings['PageTitle'] = $toolTitle;
        $bindings['ToolDetails'] = $toolDetails;
        $bindings['CommandOutput'] = $commandOutput;

        return view('admin.tools.toolProcessModular', $bindings);
    }

}
