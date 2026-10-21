<?php

namespace App\Console\Commands\GameGroup;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameGroup\Linker;
use App\Domain\GameGroup\Matcher;
use App\Models\GameGroup;
use Illuminate\Console\Command;

/**
 * Groups the Switch 1 / Switch 2 pairs that already share a link_title, as identical (#44).
 *
 * An exact link_title match is what the public "Also on" box has always trusted, so
 * grouping these adds no new risk (Ben, 2026-10-21). Any that should be enhanced are
 * changed by hand. Normalised title matches are not touched - they are suggestions on
 * the staff game groups page.
 *
 * DRY RUN BY DEFAULT. Meant to be run once by hand on prod, with a DB dump first.
 */
class BackfillLinkTitlePairs extends Command
{
    protected $signature = 'GameGroupsBackfillLinkTitlePairs
                            {--apply : Create the groups. Without it, nothing is written}';

    protected $description = 'Groups Switch 1 / Switch 2 games sharing a link_title as identical. Dry run unless --apply.';

    public function handle(Matcher $matcher, Linker $linker, GameRepository $repoGame)
    {
        $apply = (bool) $this->option('apply');

        $pairs = $matcher->linkTitlePairs();

        $report = [];
        foreach ($pairs as $pair) {
            $report[] = [
                $pair['switch1']->id,
                mb_strimwidth($pair['switch1']->title, 0, 45, '…'),
                $pair['switch2']->id,
                mb_strimwidth($pair['switch2']->title, 0, 45, '…'),
            ];
            if ($apply) {
                $linker->link($pair['switch2'], $pair['switch1'], GameGroup::MATCH_IDENTICAL);
                $repoGame->clearCacheCoreData($pair['switch1']->id);
                $repoGame->clearCacheCoreData($pair['switch2']->id);
            }
        }

        $this->table(['Switch 1', 'Title', 'Switch 2', 'Title'], $report);

        $this->newLine();
        $this->info(str_pad('Pairs:', 18).$pairs->count());
        $this->info(str_pad('Ambiguous:', 18).$matcher->ambiguousCount());

        $this->newLine();
        if ($apply) {
            $this->info('Applied. Public pages pick up the groups as their caches expire; nothing changes for these games, which already matched on link_title.');
        } else {
            $this->warn('DRY RUN - nothing written. Pass --apply to create the groups.');
        }

        return self::SUCCESS;
    }
}
