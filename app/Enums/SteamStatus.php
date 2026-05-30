<?php

namespace App\Enums;

enum SteamStatus: string
{
    case NOT_CHECKED  = 'not_checked';
    case LINKED       = 'linked';
    case NOT_ON_STEAM = 'not_on_steam';

    public function label(): string
    {
        return match($this) {
            self::NOT_CHECKED  => 'Not checked',
            self::LINKED       => 'Linked',
            self::NOT_ON_STEAM => 'Not on Steam',
        };
    }
}
