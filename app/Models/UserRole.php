<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    const ID_GAMES_MANAGER = 1;
    const ID_REVIEWS_MANAGER = 2;
    const ID_CATEGORY_MANAGER = 3;
    const ID_PARTNERSHIPS_MANAGER = 4;
    const ID_NEWS_MANAGER = 6;
    const ID_DATA_SOURCE_MANAGER = 8;

    const ROLE_GAMES_MANAGER = 'Games manager';
    const ROLE_REVIEWS_MANAGER = 'Reviews manager';
    const ROLE_CATEGORY_MANAGER = 'Category manager';
    const ROLE_PARTNERSHIPS_MANAGER = 'Partnerships manager';
    const ROLE_NEWS_MANAGER = 'News manager';
    const ROLE_DATA_SOURCE_MANAGER = 'Data source manager';

    public static function getRoleList()
    {
        return [
            self::ID_GAMES_MANAGER => self::ROLE_GAMES_MANAGER,
            self::ID_REVIEWS_MANAGER => self::ROLE_REVIEWS_MANAGER,
            self::ID_CATEGORY_MANAGER => self::ROLE_CATEGORY_MANAGER,
            self::ID_PARTNERSHIPS_MANAGER => self::ROLE_PARTNERSHIPS_MANAGER,
            self::ID_NEWS_MANAGER => self::ROLE_NEWS_MANAGER,
            self::ID_DATA_SOURCE_MANAGER => self::ROLE_DATA_SOURCE_MANAGER,
        ];
    }

    public static function getRoleFromId($roleId)
    {
        $role = null;
        switch ($roleId) {
            case self::ID_GAMES_MANAGER:
                $role = self::ROLE_GAMES_MANAGER;
                break;
            case self::ID_REVIEWS_MANAGER:
                $role = self::ROLE_REVIEWS_MANAGER;
                break;
            case self::ID_CATEGORY_MANAGER:
                $role = self::ROLE_CATEGORY_MANAGER;
                break;
            case self::ID_PARTNERSHIPS_MANAGER:
                $role = self::ROLE_PARTNERSHIPS_MANAGER;
                break;
            case self::ID_NEWS_MANAGER:
                $role = self::ROLE_NEWS_MANAGER;
                break;
            case self::ID_DATA_SOURCE_MANAGER:
                $role = self::ROLE_DATA_SOURCE_MANAGER;
                break;
        }

        return $role;
    }

    public static function getIdFromName($role)
    {
        $roleId = null;
        switch ($role) {
            case self::ROLE_GAMES_MANAGER:
                $roleId = self::ID_GAMES_MANAGER;
                break;
            case self::ROLE_REVIEWS_MANAGER:
                $roleId = self::ID_REVIEWS_MANAGER;
                break;
            case self::ROLE_CATEGORY_MANAGER:
                $roleId = self::ID_CATEGORY_MANAGER;
                break;
            case self::ROLE_PARTNERSHIPS_MANAGER:
                $roleId = self::ID_PARTNERSHIPS_MANAGER;
                break;
            case self::ROLE_NEWS_MANAGER:
                $roleId = self::ID_NEWS_MANAGER;
                break;
            case self::ROLE_DATA_SOURCE_MANAGER:
                $roleId = self::ID_DATA_SOURCE_MANAGER;
                break;
        }

        return $roleId;
    }
}
