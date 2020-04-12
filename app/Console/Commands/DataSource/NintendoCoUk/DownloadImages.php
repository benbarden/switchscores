<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Factories\DataSource\NintendoCoUk\DownloadImageFactory;

use App\Traits\SwitchServices;

class DownloadImages extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSNintendoCoUkDownloadImages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads packshots for games.';

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
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $logger->info('Loading data...');

        $dsParsedList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkWithGameId();

        $logger->info('Found '.count($dsParsedList).' item(s); processing');

        foreach ($dsParsedList as $dsItem) {

            $itemTitle = $dsItem->title;
            $gameId = $dsItem->game_id;
            $game = $this->getServiceGame()->find($gameId);

            if (!$game) {
                $logger->error($itemTitle.' - invalid game_id: '.$gameId.' - skipping');
                continue;
            }

            $logger->info('Download images for game: '.$itemTitle.' ['.$gameId.']');
            DownloadImageFactory::downloadImages($game, $dsItem);

        }

        $logger->info('Complete');
    }
}