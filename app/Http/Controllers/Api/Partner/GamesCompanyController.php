<?php

namespace App\Http\Controllers\Api\Partner;

use App\Domain\Partner\Repository as PartnerRepository;

class GamesCompanyController
{
    private $repoPartner;

    public function __construct(
        PartnerRepository $repoPartner
    )
    {
        $this->repoPartner = $repoPartner;
    }

    public function findByName()
    {
        $request = request();

        $name = $request->name;
        if (!$name) {
            return response()->json(['message' => 'Missing parameter: name'], 400);
        }

        $partners = $this->repoPartner->searchGamesCompany($name);

        return response()->json(['partners' => $partners], 200);
    }
}
