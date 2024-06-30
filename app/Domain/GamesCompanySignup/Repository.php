<?php

namespace App\Domain\GamesCompanySignup;

use App\Models\GamesCompanySignup;

class Repository
{
    /**
     * @param $id
     * @return GamesCompanySignup
     */
    public function find($id)
    {
        return GamesCompanySignup::find($id);
    }

    public function countTotal()
    {
        return GamesCompanySignup::orderBy('id')->count();
    }

}