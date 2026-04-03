<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;

use App\Domain\GameTitleHash\HashGenerator;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\Url\LinkTitle;
use App\Models\Console;
use App\Models\Game;

class GameCleanupSwitch2LinkTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:cleanup-switch2-titles {--dry-run : Preview changes without applying them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes "(Switch 2)" suffix from Switch 2 game titles and link titles';

    public function __construct(
        private GameTitleHashRepository $repoGameTitleHash,
        private HashGenerator $hashGenerator,
        private LinkTitle $linkTitleGenerator
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made');
            $this->newLine();
        }

        // Find Switch 2 games with "(Switch 2)" in title OR "-switch-2" in link_title
        $games = Game::where('console_id', Console::ID_SWITCH_2)
            ->where(function ($query) {
                $query->where('title', 'like', '% (Switch 2)%')
                    ->orWhere('link_title', 'like', '%-switch-2%');
            })
            ->get();

        if ($games->isEmpty()) {
            $this->info('No Switch 2 games found with "(Switch 2)" suffix.');
            return 0;
        }

        $this->info("Found {$games->count()} game(s) to clean up:");
        $this->newLine();

        $updated = 0;
        $skipped = 0;
        $conflicts = [];

        foreach ($games as $game) {
            $oldTitle = $game->title;
            $oldLinkTitle = $game->link_title;

            // Clean the title - remove " (Switch 2)" suffix
            $newTitle = preg_replace('/ \(Switch 2\)$/i', '', $oldTitle);

            // Generate clean link title from the cleaned title
            $newLinkTitle = $this->linkTitleGenerator->generate($newTitle);

            // Check if anything changed
            if ($oldTitle === $newTitle && $oldLinkTitle === $newLinkTitle) {
                $this->line("  [{$game->id}] {$oldTitle}: No change needed");
                $skipped++;
                continue;
            }

            // Check if the new title would conflict with another S2 game
            if ($oldTitle !== $newTitle) {
                $existingGameByTitle = Game::where('console_id', Console::ID_SWITCH_2)
                    ->where('title', $newTitle)
                    ->where('id', '!=', $game->id)
                    ->first();

                if ($existingGameByTitle) {
                    $conflicts[] = [
                        'game' => $game,
                        'old_title' => $oldTitle,
                        'new_title' => $newTitle,
                        'conflict_with' => $existingGameByTitle,
                        'conflict_type' => 'title'
                    ];
                    $this->warn("  [{$game->id}] {$oldTitle}: TITLE CONFLICT - '{$newTitle}' already used by game {$existingGameByTitle->id}");
                    $skipped++;
                    continue;
                }
            }

            // Check if the new link_title would conflict with another S2 game
            if ($oldLinkTitle !== $newLinkTitle) {
                $existingGameByLinkTitle = Game::where('console_id', Console::ID_SWITCH_2)
                    ->where('link_title', $newLinkTitle)
                    ->where('id', '!=', $game->id)
                    ->first();

                if ($existingGameByLinkTitle) {
                    $conflicts[] = [
                        'game' => $game,
                        'old_link_title' => $oldLinkTitle,
                        'new_link_title' => $newLinkTitle,
                        'conflict_with' => $existingGameByLinkTitle,
                        'conflict_type' => 'link_title'
                    ];
                    $this->warn("  [{$game->id}] {$oldTitle}: LINK TITLE CONFLICT - '{$newLinkTitle}' already used by game {$existingGameByLinkTitle->id}");
                    $skipped++;
                    continue;
                }
            }

            $this->line("  [{$game->id}]");
            if ($oldTitle !== $newTitle) {
                $this->line("      Title: {$oldTitle} -> {$newTitle}");
            }
            if ($oldLinkTitle !== $newLinkTitle) {
                $this->line("      Link:  {$oldLinkTitle} -> {$newLinkTitle}");
            }

            if (!$dryRun) {
                $game->title = $newTitle;
                $game->link_title = $newLinkTitle;
                $game->save();

                // Update the title hash if title changed
                if ($oldTitle !== $newTitle) {
                    $newTitleHash = $this->hashGenerator->generateHash($newTitle);
                    $newTitleLowercase = strtolower($newTitle);

                    // Check if hash already exists for this game
                    if (!$this->repoGameTitleHash->hashExistsForGame($newTitleHash, $game->id)) {
                        $this->repoGameTitleHash->create($newTitleLowercase, $newTitleHash, $game->id, $game->console_id);
                    }
                }
            }

            $updated++;
            $this->newLine();
        }

        $this->newLine();
        $this->info("Summary:");
        $this->line("  Updated: {$updated}");
        $this->line("  Skipped: {$skipped}");

        if (count($conflicts) > 0) {
            $this->newLine();
            $this->warn("Conflicts found - these games need manual review:");
            foreach ($conflicts as $conflict) {
                $type = $conflict['conflict_type'];
                $this->line("  [{$conflict['game']->id}] {$conflict['game']->title} ({$type} conflict with game {$conflict['conflict_with']->id})");
            }
        }

        if ($dryRun && $updated > 0) {
            $this->newLine();
            $this->info("Run without --dry-run to apply changes.");
        }

        return 0;
    }
}
