<?php

namespace App\Console\Commands\Review;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

use App\Domain\Campaign\Repository as CampaignRepository;

class CampaignUpdateProgress extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReviewCampaignUpdateProgress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates progress for review campaigns.';

    protected $repoCampaign;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        CampaignRepository $repoCampaign
    )
    {
        $this->repoCampaign = $repoCampaign;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $campaignsActive = $this->repoCampaign->getActive();

        if (!$campaignsActive) {
            $logger->error('No active campaigns!');
            return 0;
        }

        foreach ($campaignsActive as $campaign) {

            $campaignId = $campaign->id;
            $rankedCount = $this->getServiceCampaignGame()->countRankedGames($campaignId);
            $campaignGames = $this->getServiceCampaignGame()->getByCampaign($campaignId);
            $totalCount = count($campaignGames);
            if ($totalCount == 0) {
                $progress = 0;
            } else {
                $progress = round(($rankedCount / $totalCount) * 100, 2);
            }
            $progress = number_format($progress, 2);
            $campaign->progress = $progress;
            $campaign->save();

            $loggerMsg = sprintf('Campaign: %s; Total games: %s, Ranked: %s; Progress: %s', $campaignId, $totalCount, $rankedCount, $progress);
            $logger->info($loggerMsg);

        }

        $logger->info('Complete');
    }
}
