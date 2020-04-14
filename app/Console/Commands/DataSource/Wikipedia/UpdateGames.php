<?php

namespace App\Console\Commands\DataSource\Wikipedia;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Factories\DataSource\Wikipedia\UpdateGameFactory;

use App\Traits\SwitchServices;

class UpdateGames extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSWikipediaUpdateGames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates data for games linked to Wikipedia data records.';

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

        $dsParsedList = $this->getServiceDataSourceParsed()->getAllWikipediaWithGameId();

        $logger->info('Found '.count($dsParsedList).' item(s); processing');

        foreach ($dsParsedList as $dsItem) {

            $itemTitle = $dsItem->title;
            $gameId = $dsItem->game_id;
            $game = $this->getServiceGame()->find($gameId);

            if (!$game) {
                $logger->error($itemTitle.' - invalid game_id: '.$gameId.' - skipping');
                continue;
            }

            $gameImportRule = $this->getServiceGameImportRuleWikipedia()->getByGameId($gameId);

            UpdateGameFactory::doUpdate($game, $dsItem, $gameImportRule);

        }

        $logger->info('Complete');
    }
}
