<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\UserCreated;
use App\Factories\UserFactory;
use App\Factories\UserPointTransactionDirectorFactory;

use App\Domain\User\Repository as UserRepository;

class PointsForUserRegistration
{
    private $repoUser;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        UserRepository $repoUser
    )
    {
        $this->repoUser = $repoUser;
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
        $user = $this->repoUser->find($userId);
        UserFactory::addPointsForUserRegistration($user);

        // Store the transaction
        UserPointTransactionDirectorFactory::addForUserRegistration($userId);
    }
}
