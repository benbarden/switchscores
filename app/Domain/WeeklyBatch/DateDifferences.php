<?php

namespace App\Domain\WeeklyBatch;

use Illuminate\Support\Facades\DB;

/**
 * Release date differences between games and the weekly paste (#194).
 *
 * Nothing updates a game's date once it is set, so a game that moves keeps its old date. The
 * paste already records the date for every item it matches to a game; this compares the two.
 *
 * Upcoming only for now, and only the most recent batch with Upcoming pasted for each console,
 * so an older paste doesn't bring back a date that has since been fixed by hand. Year-only and
 * TBD dates are stored as null and drop out. Dates in weekly_paste_date_ignores are left out;
 * a later paste with a different date shows again.
 */
class DateDifferences
{
    public function get($gameId = null): array
    {
        if ($gameId) {
            $gameSql = 'AND g.id = ?';
            $params = [$gameId];
        } else {
            $gameSql = '';
            $params = [];
        }

        $rows = DB::select('
            SELECT DISTINCT g.id, g.title, g.link_title, g.console_id, g.eu_release_date, g.eu_is_released,
            wbi.release_date AS paste_release_date, b.batch_date,
            DATEDIFF(wbi.release_date, g.eu_release_date) AS days_moved
            FROM weekly_batch_items wbi
            JOIN weekly_batches b ON b.id = wbi.batch_id
            JOIN games g ON g.id = wbi.game_id
            JOIN (
                SELECT wbi2.console, MAX(b2.batch_date) AS batch_date
                FROM weekly_batch_items wbi2
                JOIN weekly_batches b2 ON b2.id = wbi2.batch_id
                WHERE wbi2.list_type = \'upcoming\'
                GROUP BY wbi2.console
            ) latest ON latest.console = wbi.console AND latest.batch_date = b.batch_date
            WHERE wbi.list_type = \'upcoming\'
            AND wbi.release_date IS NOT NULL
            AND NOT (g.eu_release_date <=> wbi.release_date)
            AND NOT EXISTS (
                SELECT 1 FROM weekly_paste_date_ignores i
                WHERE i.game_id = g.id AND i.release_date = wbi.release_date
            )
            '.$gameSql.'
            ORDER BY wbi.release_date, g.title
        ', $params);

        foreach ($rows as $row) {
            $row->status_warning = $this->statusWarning($row);
        }

        return $rows;
    }

    public function count(): int
    {
        return count($this->get());
    }

    /**
     * Apply only changes the date and year, so flag the rows where the game's released status
     * wouldn't fit the new date. A released game on the Upcoming list is often a different game
     * with the same title.
     */
    private function statusWarning(object $row): ?string
    {
        $isFuture = $row->paste_release_date > now()->toDateString();

        if ($isFuture && $row->eu_is_released == 1) {
            return 'Marked as released, but the new date is in the future. Check this is the same game.';
        }
        if (!$isFuture && $row->eu_is_released == 0) {
            return 'Not marked as released, but the new date has passed.';
        }
        return null;
    }
}
