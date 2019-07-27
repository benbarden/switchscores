<?php

namespace App\Factories;

use App\User;

class UserFactory
{
    public static function addToPointsBalance(User $user, $pointsToAdd)
    {
        $user->points_balance = $user->points_balance + $pointsToAdd;
        $user->save();
    }
}