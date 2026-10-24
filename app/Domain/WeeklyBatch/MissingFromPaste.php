<?php

namespace App\Domain\WeeklyBatch;

use App\Models\Console;
use App\Models\WeeklyBatchItem;
use Illuminate\Support\Facades\DB;

/**
 * Games in the DB missing from the latest weekly paste (#195).
 *
 * Upcoming only: unreleased, active games dated after the batch (or with no date) that no item
 * in their console's latest Upcoming batch points to. Games past their date belong on the New
 * list, or Games to release (#197).
 *
 * Seen means an item in the batch points to the game, or an item with no game_id matches it
 * through ExistingGameMatcher (an excluded item, say).
 *
 * The paste has no page count or total, so the latest batch is taken as complete. Callers show
 * the batch date and page count so a partial paste stands out.
 *
 * A game is checked when it has a check dated on or after the last batch it was seen in. One
 * that reappears in a later paste and goes missing again shows as not checked.
 */
class MissingFromPaste
{
    private const CONSOLES = [
        Console::ID_SWITCH_1 => 'switch-1',
        Console::ID_SWITCH_2 => 'switch-2',
    ];

    public function __construct(
        private ExistingGameMatcher $existingGameMatcher
    ) {
    }

    /**
     * Each missing game, with checked_on (null if not checked, or the check is out of date).
     */
    public function get($gameId = null): array
    {
        $rows = [];
        foreach ($this->latestBatches() as $batch) {
            $rows = array_merge($rows, $this->forBatch($batch, $gameId));
        }
        return $rows;
    }

    public function countNotChecked(): int
    {
        return count(array_filter($this->get(), fn ($row) => !$row->checked_on));
    }

    public function isMissingAndNotChecked($gameId): bool
    {
        foreach ($this->get($gameId) as $row) {
            if (!$row->checked_on) return true;
        }
        return false;
    }

    /**
     * The latest batch with Upcoming pasted for each console, with its page count.
     */
    public function latestBatches(): array
    {
        $batches = [];
        foreach (self::CONSOLES as $consoleId => $console) {
            $batch = DB::selectOne('
                SELECT b.id, b.batch_date
                FROM weekly_batches b
                WHERE EXISTS (
                    SELECT 1 FROM weekly_batch_items wbi
                    WHERE wbi.batch_id = b.id AND wbi.console = ? AND wbi.list_type = \'upcoming\'
                )
                ORDER BY b.batch_date DESC
                LIMIT 1
            ', [$console]);
            if (!$batch) continue;

            $batch->console = $console;
            $batch->console_id = $consoleId;
            $batch->page_count = DB::table('weekly_batch_raw_pages')
                ->where('batch_id', $batch->id)
                ->where('console', $console)
                ->where('list_type', 'upcoming')
                ->count();
            $batches[] = $batch;
        }
        return $batches;
    }

    private function forBatch(object $batch, $gameId): array
    {
        $params = [$batch->id, $batch->console, $batch->console_id, $batch->batch_date, $batch->batch_date];
        $gameSql = '';
        if ($gameId) {
            $gameSql = 'AND g.id = ?';
            $params[] = $gameId;
        }

        $rows = DB::select('
            SELECT g.id, g.title, g.link_title, g.console_id, g.eu_release_date, g.created_at,
            (
                SELECT MAX(b.batch_date) FROM weekly_batch_items w
                JOIN weekly_batches b ON b.id = w.batch_id
                WHERE w.game_id = g.id
            ) AS last_seen,
            c.checked_on
            FROM games g
            LEFT JOIN weekly_paste_missing_checks c ON c.game_id = g.id
            WHERE NOT EXISTS (
                SELECT 1 FROM weekly_batch_items w
                WHERE w.game_id = g.id AND w.batch_id = ? AND w.console = ? AND w.list_type = \'upcoming\'
            )
            AND g.console_id = ?
            AND g.eu_is_released = 0
            AND (g.game_status IS NULL OR g.game_status = \'active\')
            AND (g.eu_release_date IS NULL OR g.eu_release_date > ?)
            AND (g.created_at IS NULL OR DATE(g.created_at) <= ?)
            '.$gameSql.'
            ORDER BY g.eu_release_date IS NULL, g.eu_release_date, g.title
        ', $params);

        if (!$rows) return [];

        // Items with no game_id can still be a game in the DB: excluded items are never
        // matched, and items parsed before #211 could miss a renamed game
        $seenIds = [];
        $unmatched = WeeklyBatchItem::where('batch_id', $batch->id)
            ->where('console', $batch->console)
            ->where('list_type', 'upcoming')
            ->whereNull('game_id')
            ->get();
        foreach ($unmatched as $item) {
            $game = $this->existingGameMatcher->find($item->nsuid, $item->title, $item->title_raw, $batch->console_id);
            if ($game) $seenIds[$game->id] = true;
        }
        $rows = array_values(array_filter($rows, fn ($row) => !isset($seenIds[$row->id])));

        foreach ($rows as $row) {
            $row->batch_date = $batch->batch_date;
            // A check made before the game was last seen is out of date
            if ($row->checked_on && $row->last_seen && $row->last_seen > $row->checked_on) {
                $row->checked_on = null;
            }
        }
        return $rows;
    }
}
