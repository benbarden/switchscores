<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Domain\Gsc\Snapshot\GscClient;
use App\Domain\Gsc\Snapshot\SnapshotFetcher;
use App\Domain\Gsc\Snapshot\PageSnapshotBuilder;
use App\Models\GscPageSnapshot;

class FetchGscPageSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-gsc-page-snapshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stores 28-day GSC page snapshots for category and game pages.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->getName().' *************** ');

        $windowDays   = 28;
        $snapshotDate = now()->toDateString();

        $service = (new GscClient())->makeService();

        $fetcher = new SnapshotFetcher(
            $service,
            'https://www.switchscores.com/'
        );

        $builder = new PageSnapshotBuilder();

        // Categories
        $rows = $fetcher->fetch('/c/', $windowDays, 100);
        $pages = $builder->build($rows);

        $storedCategories = $this->store($pages, $snapshotDate, $windowDays);

        // Categories (newer /browse/category URLs)
        $rows = $fetcher->fetch('/browse/category', $windowDays, 100);
        $pages = $builder->build($rows);

        $storedBrowse = $this->store($pages, $snapshotDate, $windowDays);

        // Games
        $rows = $fetcher->fetch('/games/', $windowDays, 1000);
        $pages = $builder->build($rows, isGames: true);

        $storedGames = $this->store($pages, $snapshotDate, $windowDays);

        $logger->info(sprintf(
            'Snapshot %s: stored /c/ %d, /browse/category %d, /games/ %d',
            $snapshotDate, $storedCategories, $storedBrowse, $storedGames
        ));

        return Command::SUCCESS;
    }

    protected function store(array $pages, string $date, int $window): int
    {
        foreach ($pages as $url => $data) {
            GscPageSnapshot::updateOrCreate(
                [
                    'page_url'      => $url,
                    'snapshot_date' => $date,
                    'window_days'   => $window,
                ],
                $data
            );
        }

        return count($pages);
    }
}
