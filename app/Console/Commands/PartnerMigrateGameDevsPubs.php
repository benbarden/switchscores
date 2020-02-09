<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

class PartnerMigrateGameDevsPubs extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PartnerMigrateGameDevsPubs {gameId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempts to migrate legacy developer/publisher fields to the new partner links.';

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

        $servicePartner = $this->getServicePartner();
        $serviceGame = $this->getServiceGame();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        // Step 1a. Exact matches (Developers)
        $gamesToMigrate = $servicePartner->getGameDevelopersForMigration();

        $logger->info('Found '.count($gamesToMigrate).' game(s) to migrate...');

        foreach ($gamesToMigrate as $gameItem) {

            $gameId = $gameItem->game_id;
            $gameTitle = $gameItem->game_title;
            $legacyDeveloper = $gameItem->developer;
            $partnerId = $gameItem->partner_id;
            $partnerName = $gameItem->partner_name;

            if ($argGameId && ($gameId != $argGameId)) continue;

            $logger->info('**************************************************');
            $logger->info(sprintf('Processing game: %s [%s]', $gameTitle, $gameId));

            if ($serviceGameDeveloper->gameHasDeveloper($gameId, $partnerId)) {
                $logger->info('Game already has developer set - '.$partnerName);
            } else {
                $logger->info('Adding link to developer: '.$partnerName.' - id: '.$partnerId);
                $serviceGameDeveloper->createGameDeveloper($gameId, $partnerId);
            }

            $logger->info('Clearing old developer field');
            $game = $serviceGame->find($gameId);
            $serviceGame->clearOldDeveloperField($game);

        }

        // Step 1b. Exact matches (Publishers)
        $gamesToMigrate = $servicePartner->getGamePublishersForMigration();

        $logger->info('Found '.count($gamesToMigrate).' game(s) to migrate...');

        foreach ($gamesToMigrate as $gameItem) {

            $gameId = $gameItem->game_id;
            $gameTitle = $gameItem->game_title;
            $legacyPublisher = $gameItem->publisher;
            $partnerId = $gameItem->partner_id;
            $partnerName = $gameItem->partner_name;

            if ($argGameId && ($gameId != $argGameId)) continue;

            $logger->info('**************************************************');
            $logger->info(sprintf('Processing game: %s [%s]', $gameTitle, $gameId));

            if ($serviceGamePublisher->gameHasPublisher($gameId, $partnerId)) {
                $logger->info('Game already has publisher set - '.$partnerName);
            } else {
                $logger->info('Adding link to publisher: '.$partnerName.' - id: '.$partnerId);
                $serviceGamePublisher->createGamePublisher($gameId, $partnerId);
            }

            $logger->info('Clearing old publisher field');
            $game = $serviceGame->find($gameId);
            $serviceGame->clearOldPublisherField($game);

        }

    }
}
