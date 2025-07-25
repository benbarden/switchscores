<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Factories\DataSource\NintendoCoUk\UpdateGameFactory;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\GameImportRuleEshop\Repository as GameImportRuleEshopRepository;

class UpdateGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSNintendoCoUkUpdateGames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates data for games linked to eShop Europe data records.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private GameRepository $repoGame
    )
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
        $repoDataSourceParsed = new DataSourceParsedRepository();
        $repoGameImportRuleEshop = new GameImportRuleEshopRepository;

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $logger->info('Loading data...');

        $dsParsedList = $repoDataSourceParsed->getAllNintendoCoUkWithGameId();

        $logger->info('Found '.count($dsParsedList).' item(s); processing');

        foreach ($dsParsedList as $dsItem) {

            $itemTitle = $dsItem->title;
            $gameId = $dsItem->game_id;
            $game = $this->repoGame->find($gameId);

            if (!$game) {
                $logger->error($itemTitle.' - invalid game_id: '.$gameId.' - skipping');
                continue;
            }

            $gameImportRule = $repoGameImportRuleEshop->getByGameId($gameId);

            UpdateGameFactory::doUpdate($game, $dsItem, $gameImportRule);

        }

        $logger->info('Complete');
    }
}
