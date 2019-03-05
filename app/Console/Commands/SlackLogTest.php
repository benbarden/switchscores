<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SlackLogTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SlackLogTest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test logger for Slack.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $channel = env('SLACK_ALERT_CHANNEL', '');
        if ($channel) {
            \Slack::to('#'.$channel)->send('Testing Slack log messages');
        }
    }
}
