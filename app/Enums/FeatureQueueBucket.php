<?php

namespace App\Enums;

enum FeatureQueueBucket: string
{
    case NEEDS_2_REVIEWS = 'needs-2-reviews';
    case NEEDS_1_REVIEW  = 'needs-1-review';
    case NEEDS_0_REVIEWS = 'needs-0-reviews';
    case NEWLY_RANKED    = 'newly-ranked';
    case FORGOTTEN_GEM   = 'forgotten-gem';

    public static function tryFromSlug(?string $slug): ?self
    {
        foreach (self::cases() as $c) {
            if ($c->value === $slug) return $c;
        }
        return null;
    }

    public function label(): string
    {
        return match($this) {
            self::NEEDS_2_REVIEWS => 'Games that need one more review',
            self::NEEDS_1_REVIEW  => 'Promising games (only 1 review so far)',
            self::NEEDS_0_REVIEWS => 'New/overlooked games (no reviews yet)',
            self::NEWLY_RANKED    => 'Newly ranked games',
            self::FORGOTTEN_GEM   => 'Forgotten gems worth a look',
        };
    }
}