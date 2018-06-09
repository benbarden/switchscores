<?php


namespace App\Services;

use App\UserListItem;


class UserListItemService
{
    public function find($id)
    {
        return UserListItem::find($id);
    }

    public function getByList($listId)
    {
        $userListItems = UserListItem::where('list_id', $listId)
            ->get();
        return $userListItems;
    }

    public function getByListAndGame($listId, $gameId)
    {
        $userListItem = UserListItem::
            where('list_id', $listId)
            ->where('game_id', $gameId)
            ->first();
        return $userListItem;
    }

    public function getByGame($gameId)
    {
        $userListItem = UserListItem::
            where('game_id', $gameId)
            ->get();
        return $userListItem;
    }
}