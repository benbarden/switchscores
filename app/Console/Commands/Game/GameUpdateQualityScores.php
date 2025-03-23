<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Construction\GameQualityScore\QualityScoreBuilder;
use App\Construction\GameQualityScore\QualityScoreDirector;

use App\Domain\Game\Repository as GameRepository;

use App\Traits\SwitchServices;

class GameUpdateQualityScores extends Command
{
    use SwitchServices;

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
        $argGameId = $this->argument('gameId');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $repoGame = new GameRepository();

        if ($argGameId) {
            $gameIds = $this->getServiceGame()->getByIdList($argGameId);
        } else {
            $gameIds = $this->getServiceGame()->getAll();
        }

        foreach ($gameIds as $gameRow) {

            $game = $repoGame->find($gameRow->id);
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

            $importRuleEshop = $this->getServiceGameImportRuleEshop()->getByGameId($gameId);
            if ($importRuleEshop) {
                $qsDirector->setImportRuleEshop($importRuleEshop);
            }

            $dsParsedNintendoCoUk = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
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
