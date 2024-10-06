<?php

namespace App\Domain\PersonalAccessToken;

use App\Models\PersonalAccessToken;

class Repo
{
    public function getByTokenableId($id)
    {
        return PersonalAccessToken::where('tokenable_id', $id)->get();
    }

    public function find($id)
    {
        return PersonalAccessToken::find($id);
    }

    public function delete($id)
    {
        $token = PersonalAccessToken::find($id);
        if ($token) $token->delete();
    }
}