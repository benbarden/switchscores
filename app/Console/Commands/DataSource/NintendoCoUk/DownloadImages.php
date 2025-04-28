<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Factories\DataSource\NintendoCoUk\DownloadImageFactory;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;

class DownloadImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSNintendoCoUkDownloadImages {gameId?}';

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
        $repoGame = new GameRepository();

        $repoDataSourceParsed = new DataSourceParsedRepository();

        $argGameId = $this->argument('gameId');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $logger->info('Loading data...');

        if ($argGameId) {
            $importGameId = $argGameId;
        } else {
            $importGameId = null;
        }

        if ($importGameId) {

            $logger->info('Importing for game id: '.$importGameId);

            $dsItem = $repoDataSourceParsed->getSourceNintendoCoUkForGame($importGameId);

            if ($dsItem) {
                $logger->info('Processing item...');
                $itemTitle = $dsItem->title;
                $gameId = $dsItem->game_id;
                $game = $repoGame->find($gameId);

                if ($game) {
                    $logger->info('Download images for game: '.$itemTitle.' ['.$gameId.']');
                    DownloadImageFactory::downloadImages($game, $dsItem);
                } else {
                    $logger->error($itemTitle.' - invalid game_id: '.$gameId.' - skipping');
                }
            } else {
                $logger->info('Cannot find dsItem for game');
            }

        } else {

            $dsParsedList = $repoDataSourceParsed->getAllNintendoCoUkWithGameId();

            $logger->info('Found '.count($dsParsedList).' item(s); processing');

            foreach ($dsParsedList as $dsItem) {

                $itemTitle = $dsItem->title;
                $gameId = $dsItem->game_id;
                $game = $repoGame->find($gameId);

                if (!$game) {
                    $logger->error($itemTitle.' - invalid game_id: '.$gameId.' - skipping');
                    continue;
                }

                $logger->info('Download images for game: '.$itemTitle.' ['.$gameId.']');
                DownloadImageFactory::downloadImages($game, $dsItem);

            }

        }

        $logger->info('Complete');
    }
}
