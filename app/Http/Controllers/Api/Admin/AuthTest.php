<?php

namespace App\Http\Controllers\Api\Admin;

class AuthTest
{
    public function quickCheck()
    {
        return response()->json(['message' => 'OK'], 200);
    }
}
