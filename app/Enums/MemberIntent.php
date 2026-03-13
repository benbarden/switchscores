<?php

namespace App\Enums;

enum MemberIntent: string
{
    case WISHLIST_ADD = 'wishlist-add';
    case COLLECTION_ADD = 'collection-add';
    case QUICK_REVIEW = 'quick-review';

    public function label(): string
    {
        return match($this) {
            self::WISHLIST_ADD => 'add this game to your wishlist',
            self::COLLECTION_ADD => 'add this game to your collection',
            self::QUICK_REVIEW => 'add a quick review for this game',
        };
    }

    public function successMessage(): string
    {
        return match($this) {
            self::WISHLIST_ADD => 'Game added to your wishlist!',
            self::COLLECTION_ADD => 'Redirecting to add game to your collection...',
            self::QUICK_REVIEW => 'Redirecting to add your quick review...',
        };
    }
}
