<?php

namespace App\Console\Commands\News;

use App\Models\NewsDbUpdate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

use App\Domain\GameCalendar\AllowedDates;
use App\Domain\NewsDbUpdate\Repository as NewsDbUpdateRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;

class RefreshNewsDbUpdateStats extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RefreshNewsDbUpdateStats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes stats for NewsDbUpdate entries.';

    protected $allowedDates;
    protected $repoNewsDbUpdate;
    protected $repoGameStats;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        AllowedDates $allowedDates,
        NewsDbUpdateRepository $repoNewsDbUpdate,
        GameStatsRepository $repoGameStats
    )
    {
        $this->allowedDates = $allowedDates;
        $this->repoNewsDbUpdate = $repoNewsDbUpdate;
        $this->repoGameStats = $repoGameStats;
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

        $allowedYears = $this->allowedDates->getAllowedYears();

        foreach ($allowedYears as $year) {

            if ($year == 2017) {
                $startWeek = NewsDbUpdate::SWITCH_LAUNCH_WEEK_2017;
            } else {
                $startWeek = 1;
            }

            $logger->info('Starting year: '.$year);

            for ($week = $startWeek; $week < 53; $week++) {

                $statsStandard = $this->repoGameStats->totalYearWeekStandardQuality($year, $week);
                $statsLowQuality = $this->repoGameStats->totalYearWeekLowQuality($year, $week);

                $logger->info('Week '.$week.' --- Standard quality: '.$statsStandard.' ; Low quality: '.$statsLowQuality);

                $newsDbUpdate = $this->repoNewsDbUpdate->get($year, $week);

                if ($newsDbUpdate) {

                    // Record exists
                    $this->repoNewsDbUpdate->update($newsDbUpdate, $statsStandard, $statsLowQuality);

                } else {

                    // We need to create it first, but only if it has games
                    if (($statsStandard == 0) && ($statsLowQuality == 0)) {
                        $logger->error('No games; skipping');
                        continue;
                    }
                    $logger->info('Creating entry for '.$year.' week '.$week);
                    $newsDbUpdate = $this->repoNewsDbUpdate->create($year, $week);
                    $this->repoNewsDbUpdate->update($newsDbUpdate, $statsStandard, $statsLowQuality);

                }

            }

        }

        $logger->info('Complete');
    }
}
