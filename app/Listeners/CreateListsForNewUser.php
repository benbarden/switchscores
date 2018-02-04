<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\UserList;
use App\Services\UserListService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateListsForNewUser
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
        $properties = [
            'user_id' => $userId,
        ];
        $jsonProperties = json_encode($properties);

        $userListService = resolve('Services\UserListService');
        /* @var $userListService UserListService */

        $userList = new UserList;
        $userList->user_id = $userId;
        $userList->list_type = UserList::LIST_TYPE_OWNED;
        $userList->list_name = 'Owned list';
        $userList->list_status = 1;
        $userList->save();

        $userList = new UserList;
        $userList->user_id = $userId;
        $userList->list_type = UserList::LIST_TYPE_WISHLIST;
        $userList->list_name = 'Wishlist';
        $userList->list_status = 1;
        $userList->save();
    }
}
