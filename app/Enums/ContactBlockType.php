<?php

namespace App\Enums;

enum ContactBlockType: string
{
    case EMAIL  = 'email';
    case DOMAIN = 'domain';

    public static function options(): array
    {
        return [
            self::EMAIL->value  => 'Email address',
            self::DOMAIN->value => 'Whole domain',
        ];
    }
}
