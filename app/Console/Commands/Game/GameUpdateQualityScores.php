<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Construction\GameQualityScore\QualityScoreBuilder;
use App\Construction\GameQualityScore\QualityScoreDirector;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\DbQueries as DbGame;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\GameImportRuleEshop\Repository as GameImportRuleEshopRepository;

class GameUpdateQualityScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GameUpdateQualityScores {gameId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates quality scores for games.';

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
        $argGameId = $this->argument('gameId');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $dbGame = new DbGame();
        $repoDataSourceParsed = new DataSourceParsedRepository();
        $repoGameImportRuleEshop = new GameImportRuleEshopRepository;

        if ($argGameId) {
            $gameIds = $this->repoGame->getByIdList($argGameId);
        } else {
            $gameIds = $dbGame->getAll();
        }

        foreach ($gameIds as $gameRow) {

            $game = $this->repoGame->find($gameRow->id);
            $gameId = $game->id;

            //$logger->info('Processing game: '.$game->id.' - '.$game->title);

            if ($argGameId) {
                // Make it more verbose when running against individual games
                $qsDirector = new QualityScoreDirector($logger);
            } else {
                $qsDirector = new QualityScoreDirector();
            }
            $qsBuilder = new QualityScoreBuilder();
            $qsDirector->setBuilder($qsBuilder);

            $qsDirector->setGame($game);

            $importRuleEshop = $repoGameImportRuleEshop->getByGameId($gameId);
            if ($importRuleEshop) {
                $qsDirector->setImportRuleEshop($importRuleEshop);
            }

            $dsParsedNintendoCoUk = $repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);
            if ($dsParsedNintendoCoUk) {
                $qsDirector->setDataSourceParsedNintendoCoUk($dsParsedNintendoCoUk);
            }

            $gameQualityScore = $game->gameQualityScore;
            if ($gameQualityScore) {
                $qsDirector->buildExisting($gameQualityScore);
            } else {
                $qsDirector->buildNew();
            }
            $qsDirector->getGameQualityScore()->save();

        }

        $logger->info('Complete');
    }
}
