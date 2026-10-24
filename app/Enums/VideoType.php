<?php

namespace App\Enums;

/**
 * games.video_type (#36). The column is nullable: null means not set yet (the staff "no video
 * type" list), which is different from NONE.
 */
enum VideoType: int
{
    case NONE     = 0;
    case TRAILER  = 1;
    case GAMEPLAY = 2;

    public static function options(): array
    {
        return [
            self::NONE->value     => 'No video',
            self::TRAILER->value  => 'Trailer',
            self::GAMEPLAY->value => 'Gameplay',
        ];
    }

    /**
     * For the public game page button: "Watch trailer", "Watch gameplay". Empty for NONE.
     */
    public function publicLabel(): string
    {
        return match($this) {
            self::NONE     => '',
            self::TRAILER  => 'trailer',
            self::GAMEPLAY => 'gameplay',
        };
    }
}
