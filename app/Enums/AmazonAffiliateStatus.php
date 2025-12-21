<?php

namespace App\Enums;

enum AmazonAffiliateStatus: string
{
    case UNCHECKED  = 'unchecked';
    case LINKED     = 'linked';
    case NO_PRODUCT = 'no_product';
    case IGNORED    = 'ignored';

    public static function options(): array
    {
        return [
            self::UNCHECKED->value  => 'Unchecked',
            self::LINKED->value     => 'Linked',
            self::NO_PRODUCT->value => 'No product',
            self::IGNORED->value    => 'Ignored',
        ];
    }
}