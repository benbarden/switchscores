<?php


namespace App\Services;

use App\UserList;


class UserListService
{
    public function delete($listId)
    {
        UserList::where('id', $listId)->delete();
    }

    public function find($id)
    {
        return UserList::find($id);
    }

    public function getAllByUser($userId)
    {
        $userList = UserList::where('user_id', $userId)
            ->get();
        return $userList;
    }

    public function getAllByUserAndType($userId, $typeId)
    {
        $userList = UserList::where('user_id', $userId)
            ->where('list_type', $typeId)
            ->get();
        return $userList;
    }

    public function getOneByUserAndType($userId, $typeId)
    {
        $userList = UserList::where('user_id', $userId)
            ->where('list_type', $typeId)
            ->first();
        return $userList;
    }

    public function getOwnedListByUser($userId)
    {
        $typeId = UserList::LIST_TYPE_OWNED;
        return $this->getOneByUserAndType($userId, $typeId);
    }

    public function getWishListByUser($userId)
    {
        $typeId = UserList::LIST_TYPE_WISHLIST;
        return $this->getOneByUserAndType($userId, $typeId);
    }
}