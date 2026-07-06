<?php

namespace App\Enums;

enum ContactRequestType: string
{
    case CORRECTION   = 'correction';
    case PARTNERSHIP  = 'partnership';
    case BUSINESS     = 'business';
    case OTHER        = 'other';

    public static function options(): array
    {
        return [
            self::CORRECTION->value  => 'Correction / data fix (wrong price, wrong info, missing game)',
            self::PARTNERSHIP->value => 'Review site partnership / feed',
            self::BUSINESS->value    => 'Business / advertising / affiliate',
            self::OTHER->value       => 'Something else',
        ];
    }

    public function label(): string
    {
        return self::options()[$this->value];
    }
}
