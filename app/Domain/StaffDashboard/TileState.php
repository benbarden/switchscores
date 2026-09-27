<?php

namespace App\Domain\StaffDashboard;

use Carbon\Carbon;

/**
 * The state of a dashboard tile, and the words shown on its chip. The chip always carries a text
 * label, so a tile's state never relies on colour alone.
 */
class TileState
{
    const OK = 'ok';
    const AMBER = 'amber';
    const RED = 'red';
    const NONE = 'none';

    /**
     * State for a queue, from the age of its oldest item.
     *
     * @param Carbon|null $oldest when the oldest waiting item arrived; null for an empty queue
     * @param int|null $amberDays older than this many whole days turns amber (null: never)
     * @param int|null $redDays older than this many whole days turns red (null: never)
     */
    public static function forAge(?Carbon $oldest, ?int $amberDays, ?int $redDays, ?Carbon $now = null): string
    {
        if ($oldest === null) {
            return self::OK;
        }
        $ageDays = self::ageInDays($oldest, $now);

        if ($redDays !== null && $ageDays > $redDays) {
            return self::RED;
        }
        if ($amberDays !== null && $ageDays > $amberDays) {
            return self::AMBER;
        }
        return self::OK;
    }

    public static function ageInDays(Carbon $since, ?Carbon $now = null): int
    {
        $now = $now ?: Carbon::now();
        return (int) floor($since->diffInSeconds($now, false) / 86400);
    }

    /**
     * "today", "1 day", "4 days" - for a tile's context line.
     */
    public static function describeAge(Carbon $since, ?Carbon $now = null): string
    {
        $days = self::ageInDays($since, $now);
        if ($days <= 0) {
            return 'today';
        }
        return $days === 1 ? '1 day' : $days.' days';
    }

    public static function queueLabel(string $state): string
    {
        return match ($state) {
            self::RED => 'Overdue',
            self::AMBER => 'Waiting',
            self::OK => 'On track',
            default => '',
        };
    }
}
