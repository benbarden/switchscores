<?php


namespace App\Domain\IntegrityCheck;

use App\IntegrityCheck;
use App\IntegrityCheckResult;

use App\Domain\IntegrityCheck\Repository as IntegrityCheckRepository;

class UpdateResults
{
    protected $integrityCheck;

    protected $repoIntegrityCheck;

    public function __construct(
        IntegrityCheckRepository $repoIntegrityCheck
    )
    {
        $this->repoIntegrityCheck = $repoIntegrityCheck;
    }

    public function setIntegrityCheck(IntegrityCheck $integrityCheck)
    {
        $this->integrityCheck = $integrityCheck;
    }

    private function doGenericUpdate($gameList)
    {
        $icResult = new IntegrityCheckResult;
        $icResult->check_id = $this->integrityCheck->id;

        $failureCount = count($gameList);

        if ($failureCount == 0) {
            // No failures
            $this->integrityCheck->is_passing = 1;
        } else {
            // Some failures
            $this->integrityCheck->is_passing = 0;
            $idList = $gameList->pluck('id');
            $icResult->id_list = $idList;
        }

        $this->integrityCheck->failing_count = $failureCount;
        $this->integrityCheck->save();
        $icResult->is_passing = $this->integrityCheck->is_passing;
        $icResult->failing_count = $this->integrityCheck->failing_count;
        $icResult->save();
    }

    public function doGameMissingRank()
    {
        $gameList = $this->repoIntegrityCheck->getGameMissingRank();
        $this->doGenericUpdate($gameList);
    }

    public function doGameNoReleaseYear()
    {
        $gameList = $this->repoIntegrityCheck->getGameNoReleaseYear();
        $this->doGenericUpdate($gameList);
    }

    public function doGameWrongReleaseYear()
    {
        $gameList = $this->repoIntegrityCheck->getGameWrongReleaseYear();
        $this->doGenericUpdate($gameList);
    }
}