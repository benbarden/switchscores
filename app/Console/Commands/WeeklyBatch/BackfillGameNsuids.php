<?php

namespace App\Console\Commands\WeeklyBatch;

use App\Models\Game;
use App\Models\WeeklyBatchItem;
use Illuminate\Console\Command;

/**
 * Copies the NSUID from imported weekly update items onto their games (#211). Until #211 the
 * import never saved it, so no game had one and the paste could only match games by title.
 *
 * Only imported items: the game was created from that item, so the NSUID is certainly its own.
 * Items matched as already in the DB are left out, since a title match can be the wrong game.
 * A game that already has an NSUID is never changed, and an NSUID already on another game on
 * the same console is reported, not copied.
 *
 * DRY RUN BY DEFAULT. Meant to be run once by hand on prod.
 */
class BackfillGameNsuids extends Command
{
    protected $signature = 'WeeklyBatchBackfillGameNsuids
                            {--apply : Save the NSUIDs. Without it, nothing is written}';

    protected $description = 'Copies NSUIDs from imported weekly update items onto their games. Dry run unless --apply.';

    public function handle()
    {
        $apply = (bool) $this->option('apply');

        // Latest imported item per game, in case a game was somehow imported twice
        $items = WeeklyBatchItem::where('item_status', WeeklyBatchItem::STATUS_IMPORTED)
            ->whereNotNull('nsuid')
            ->whereNotNull('game_id')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('game_id');

        $saved = $alreadySet = $clashes = $missing = 0;
        $report = [];

        foreach ($items as $item) {
            $game = Game::find($item->game_id);
            if (!$game) {
                $missing++;
                continue;
            }
            if ($game->eshop_europe_nsuid) {
                $alreadySet++;
                continue;
            }

            $other = Game::where('eshop_europe_nsuid', $item->nsuid)
                ->where('console_id', $game->console_id)
                ->where('id', '!=', $game->id)
                ->first();
            if ($other) {
                $clashes++;
                $report[] = [$game->id, $game->title, $item->nsuid, 'Skipped: already on game '.$other->id];
                continue;
            }

            if ($apply) {
                $game->eshop_europe_nsuid = $item->nsuid;
                $game->save();
            }
            $saved++;
        }

        if ($report) {
            $this->table(['Game', 'Title', 'NSUID', 'Result'], $report);
            $this->newLine();
        }

        $this->info(str_pad($apply ? 'Saved:' : 'Would save:', 22).$saved);
        $this->info(str_pad('Already had one:', 22).$alreadySet);
        $this->info(str_pad('Skipped (clash):', 22).$clashes);
        $this->info(str_pad('Game deleted:', 22).$missing);

        $this->newLine();
        if ($apply) {
            $this->info('Applied. The next paste matches these games by NSUID.');
        } else {
            $this->warn('DRY RUN - nothing written. Pass --apply to save the NSUIDs.');
        }
    }
}
