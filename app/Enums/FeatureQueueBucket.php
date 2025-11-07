<?php

namespace App\Enums;

enum FeatureQueueBucket: string
{
    case HAS_2_REVIEWS = 'has-2-reviews';
    case HAS_1_REVIEW  = 'has-1-review';
    case HAS_0_REVIEWS = 'has-0-reviews';
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
            self::HAS_2_REVIEWS => 'Games that need one more review',
            self::HAS_1_REVIEW  => 'Promising games with one review',
            self::HAS_0_REVIEWS => 'Games with no reviews yet',
            self::NEWLY_RANKED    => 'Newly ranked games',
            self::FORGOTTEN_GEM   => 'Forgotten gems worth a look',
        };
    }
}