<?php

namespace App\Enums;

enum ContactStatus: string
{
    case NEW      = 'new';
    case BLOCKED  = 'blocked';
    case ARCHIVED = 'archived';

    public static function options(): array
    {
        return [
            self::NEW->value      => 'New',
            self::BLOCKED->value  => 'Blocked',
            self::ARCHIVED->value => 'Archived',
        ];
    }
}
