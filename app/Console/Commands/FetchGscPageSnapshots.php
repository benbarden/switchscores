<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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

        $this->store($pages, $snapshotDate, $windowDays);

        // Games
        $rows = $fetcher->fetch('/games/', $windowDays, 1000);
        $pages = $builder->build($rows, isGames: true);

        $this->store($pages, $snapshotDate, $windowDays);

        return Command::SUCCESS;
    }

    protected function store(array $pages, string $date, int $window)
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
    }
}
