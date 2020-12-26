<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\UserCreated;
use App\Factories\UserFactory;
use App\Factories\UserPointTransactionDirectorFactory;

use App\Traits\SwitchServices;

class PointsForUserRegistration
{
    use SwitchServices;

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

        // Credit points
        $user = $this->getServiceUser()->find($userId);
        UserFactory::addPointsForUserRegistration($user);

        // Store the transaction
        UserPointTransactionDirectorFactory::addForUserRegistration($userId);
    }
}
