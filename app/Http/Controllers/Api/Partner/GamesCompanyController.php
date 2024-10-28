<?php

namespace App\Http\Controllers\Api\Partner;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;

class GamesCompanyController
{
    public function __construct(
        private GamesCompanyRepository $repoGamesCompany
    )
    {
    }

    public function findByName()
    {
        $request = request();

        $name = $request->name;
        if (!$name) {
            return response()->json(['message' => 'Missing parameter: name'], 400);
        }

        $partners = $this->repoGamesCompany->searchGamesCompany($name);

        return response()->json(['partners' => $partners], 200);
    }
}
