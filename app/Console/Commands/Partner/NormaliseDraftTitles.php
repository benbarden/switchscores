<?php

namespace App\Console\Commands\Partner;

use App\Models\ReviewDraft;

use App\Domain\ReviewDraft\ImportByFeed;

use App\Domain\Game\Repository as RepoGame;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormaliseDraftTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PartnerNormaliseDraftTitles {--dry-run : Report what would change without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans invisible characters and stray whitespace out of existing review draft titles, applying the same normalisation the importer now does.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run - no changes will be written.');
        }

        // Match on the raw bytes: utf8mb4 collations treat a non-breaking space as equal to a
        // normal space, so a plain LIKE reports almost every row.
        $drafts = ReviewDraft::whereRaw("HEX(item_title) LIKE '%C2A0%'")
            ->orWhereRaw("HEX(item_title) LIKE '%E2808B%'")
            ->orWhereRaw("HEX(item_title) LIKE '%EFBBBF%'")
            ->orWhereRaw('item_title <> TRIM(item_title)')
            ->get();

        if ($drafts->isEmpty()) {
            $this->info('Nothing to normalise.');
            return 0;
        }

        $importByFeed = new ImportByFeed(resolve(RepoGame::class));

        $changed = 0;

        foreach ($drafts as $draft) {

            $cleanTitle = $importByFeed->cleanUpTitle($draft->item_title);

            if ($cleanTitle === $draft->item_title) {
                continue;
            }

            $this->line(sprintf('Draft %d: [%s] -> [%s]', $draft->id, $draft->item_title, $cleanTitle));

            if (!$dryRun) {
                $draft->item_title = $cleanTitle;
                $draft->save();
            }

            $changed++;
        }

        $this->info(sprintf('Done. %d draft title(s) normalised.', $changed));

        return 0;
    }
}
