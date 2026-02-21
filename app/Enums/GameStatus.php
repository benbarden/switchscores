<?php

namespace App\Enums;

enum GameStatus: string
{
    case ACTIVE       = 'active';
    case DELISTED     = 'delisted';
    case SOFT_DELETED = 'soft_deleted';

    public static function options(): array
    {
        return [
            self::ACTIVE->value       => 'Active',
            self::DELISTED->value     => 'De-listed',
            self::SOFT_DELETED->value => 'Soft deleted',
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::ACTIVE       => 'Active',
            self::DELISTED     => 'De-listed',
            self::SOFT_DELETED => 'Soft deleted',
        };
    }
}
