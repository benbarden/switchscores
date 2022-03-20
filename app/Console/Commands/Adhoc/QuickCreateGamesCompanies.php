<?php

namespace App\Console\Commands\Adhoc;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;
use App\Factories\GamesCompanyFactory;

class QuickCreateGamesCompanies extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AdhocMigrateLegacyDevsWithKnownPartners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adhoc job';

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

        $partnerFile = storage_path('app/quick-create-games-companies.txt');

        if (!file_exists($partnerFile)) {
            $logger->error('Cannot find file: '.$partnerFile);
            return 0;
        }

        $partnerData = file_get_contents($partnerFile);
        if (!$partnerData) {
            $logger->error('Data file is empty');
            return 0;
        }

        $partners = explode("\n", $partnerData);

        foreach ($partners as $partnerName) {

            if (!$partnerName) {
                $logger->warning('Blank name; skipping');
                continue;
            }

            $partner = $this->getServicePartner()->getByName($partnerName);
            if ($partner) {
                $logger->warning('Partner exists - ' . $partnerName . '; skipping');
                continue;
            }

            $logger->info('Creating partner: '.$partnerName);
            $partner = GamesCompanyFactory::createActiveNameOnly($partnerName);
            $partner->save();

        }

        $logger->info('Complete');
    }
}
