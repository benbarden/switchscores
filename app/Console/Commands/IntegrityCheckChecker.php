<?php

namespace App\Console\Commands;

use App\Domain\IntegrityCheck\Repository as IntegrityCheckRepository;
use App\Domain\IntegrityCheck\UpdateResults as IntegrityCheckUpdateResults;
use App\Models\IntegrityCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IntegrityCheckChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'IntegrityCheckChecker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates integrity checks.';

    protected $repoIntegrityCheck;
    protected $updateIntegrityCheck;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        IntegrityCheckRepository $repoIntegrityCheck,
        IntegrityCheckUpdateResults $updateIntegrityCheck
    )
    {
        $this->repoIntegrityCheck = $repoIntegrityCheck;
        $this->updateIntegrityCheck = $updateIntegrityCheck;
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

        $integrityCheck = $this->repoIntegrityCheck->getByName(IntegrityCheck::GAME_MISSING_RANK);
        $this->updateIntegrityCheck->setIntegrityCheck($integrityCheck);
        $this->updateIntegrityCheck->doGameMissingRank();

        $integrityCheck = $this->repoIntegrityCheck->getByName(IntegrityCheck::GAME_NO_RELEASE_YEAR);
        $this->updateIntegrityCheck->setIntegrityCheck($integrityCheck);
        $this->updateIntegrityCheck->doGameNoReleaseYear();

        $integrityCheck = $this->repoIntegrityCheck->getByName(IntegrityCheck::GAME_WRONG_RELEASE_YEAR);
        $this->updateIntegrityCheck->setIntegrityCheck($integrityCheck);
        $this->updateIntegrityCheck->doGameWrongReleaseYear();

    }
}
