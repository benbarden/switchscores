<?php

namespace App\Factories;

use App\Models\UserPointTransaction;
use App\User;

class UserFactory
{
    public static function addToPointsBalance(User $user, $pointsToAdd)
    {
        $user->points_balance = $user->points_balance + $pointsToAdd;
        $user->save();
    }

    public static function addPointsForUserRegistration(User $user)
    {
        $pointsToAdd = UserPointTransaction::POINTS_REGISTER;
        self::addToPointsBalance($user, $pointsToAdd);
    }

    public static function addPointsForGameCategorySuggestion(User $user)
    {
        $pointsToAdd = UserPointTransaction::POINTS_DB_EDIT;
        self::addToPointsBalance($user, $pointsToAdd);
    }

    public static function addPointsForQuickReview(User $user)
    {
        $pointsToAdd = UserPointTransaction::POINTS_QUICK_REVIEW_ADD;
        self::addToPointsBalance($user, $pointsToAdd);
    }
}