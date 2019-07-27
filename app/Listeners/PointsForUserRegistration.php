<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Factories\UserFactory;
use App\Factories\UserPointTransactionDirectorFactory;
use App\UserPointTransaction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PointsForUserRegistration
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserCreated  $event
     * @return void
     */
    public function handle(UserCreated $event)
    {
        $userId = $event->user->id;

        $pointsToAdd = UserPointTransaction::POINTS_REGISTER;

        // Give the user some points
        UserFactory::addToPointsBalance($event->user, $pointsToAdd);

        // Store the transaction
        $params = UserPointTransactionDirectorFactory::buildParams(
            $userId,
            UserPointTransaction::ACTION_TYPE_REGISTER,
            null,
            $pointsToAdd,
            null
        );
        UserPointTransactionDirectorFactory::createNew($params);
    }
}
