<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use App\Models\DataSource;
use App\Models\DataSourceIgnore;
use App\Models\DataSourceParsed;

/**
 * Removes parsed records for unannounced titles that were never linked to a game.
 *
 * Nintendo returns the literal string "TBD" as the release date for unannounced titles, which
 * parseReleaseDate() cannot parse, so release_date_eu lands null. ImportParseLink no longer
 * imports these, and the "No EU date" tab on the unlinked page has been retired - but the rows
 * already in the table would otherwise linger, still counted by getAllNintendoCoUkWithNoGameId()
 * on the staff dashboard, the data-sources dashboard and the games editor dropdown, with no page
 * left to view them.
 *
 * Any matching entries on the ignore list go too, since they would keep showing on
 * /staff/data-sources/nintendo-co-uk/ignored for records that no longer exist.
 *
 * SAFETY: only records with game_id IS NULL are touched. A record attached to a game is never
 * deleted, whatever its release date. Scoped to Nintendo.co.uk - Wikipedia (source 4) has its
 * own dateless rows which are not in scope here.
 */
class PurgeUnannouncedUnlinked extends Command
{
    protected $signature = 'DSNintendoCoUkPurgeUnannouncedUnlinked {--dry-run : Report what would be deleted without deleting it}';

    protected $description = 'Deletes unlinked parsed records with no EU release date (unannounced titles), plus any matching ignore-list entries.';

    public function handle()
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        $dryRun = $this->option('dry-run');

        $records = DataSourceParsed::where('source_id', $sourceId)
            ->whereNull('release_date_eu')
            ->whereNull('game_id')
            ->orderBy('console_id')
            ->orderBy('link_id')
            ->get();

        if ($records->isEmpty()) {
            $this->info('Nothing to purge.');
            return self::SUCCESS;
        }

        $linkIds = $records->pluck('link_id')->filter()->all();

        $ignoreRecords = DataSourceIgnore::where('source_id', $sourceId)
            ->whereIn('link_id', $linkIds)
            ->get();

        $this->info('Parsed records to delete: '.$records->count());
        $this->info('Ignore-list entries to delete: '.$ignoreRecords->count());
        $this->newLine();

        $this->table(
            ['link_id', 'console', 'title'],
            $records->map(fn ($r) => [$r->link_id, $r->console_id, $r->title])->all()
        );

        if ($dryRun) {
            $this->warn('Dry run - nothing deleted.');
            return self::SUCCESS;
        }

        $deletedIgnores = DataSourceIgnore::where('source_id', $sourceId)
            ->whereIn('link_id', $linkIds)
            ->delete();

        $deletedParsed = DataSourceParsed::where('source_id', $sourceId)
            ->whereNull('release_date_eu')
            ->whereNull('game_id')
            ->delete();

        $this->info('Deleted '.$deletedParsed.' parsed record(s).');
        $this->info('Deleted '.$deletedIgnores.' ignore-list entry(ies).');

        return self::SUCCESS;
    }
}
