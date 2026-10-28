<?php

namespace App\Domain\GameLists;

use App\Models\Game;

/**
 * Related games for the public game page: one list from the game's series, collection and
 * category, most specific first, with no duplicates.
 *
 * Each source gives up to PER_SOURCE games (ranked, rated 7+, released, same console - see
 * the Repository's relatedBy* methods), and the list stops at MAX_GAMES. Each game carries
 * the name of the source it came from, for its label; every source gets a "View all" link.
 */
class RelatedGames
{
    const PER_SOURCE = 4;
    const MAX_GAMES = 8;

    public function __construct(private Repository $repoGameLists)
    {
    }

    /**
     * @return array{games: array<int, array{game: Game, label: string}>, sources: array<int, array{name: string, route: string, params: array}>, mixed: bool, heading: string}
     */
    public function forGame(Game $game): array
    {
        $sources = [];

        if ($game->series_id && $game->series) {
            $sources[] = [
                'name' => $game->series->series,
                'route' => 'browse.bySeries.page',
                'params' => ['series' => $game->series->link_title],
                'games' => $this->repoGameLists->relatedBySeries(
                    $game->console_id, $game->series_id, $game->id, self::PER_SOURCE),
            ];
        }
        if ($game->collection_id && $game->gameCollection) {
            $sources[] = [
                'name' => $game->gameCollection->name,
                'route' => 'browse.byCollection.page',
                'params' => ['collection' => $game->gameCollection->link_title],
                'games' => $this->repoGameLists->relatedByCollection(
                    $game->console_id, $game->collection_id, $game->id, self::PER_SOURCE),
            ];
        }
        if ($game->category_id && $game->category) {
            $sources[] = [
                'name' => $game->category->name,
                'route' => 'browse.byCategory.list',
                'params' => ['category' => $game->category->link_title],
                'games' => $this->repoGameLists->relatedByCategory(
                    $game->console_id, $game->category_id, $game->id, self::PER_SOURCE),
            ];
        }

        $games = [];
        foreach ($sources as $source) {
            foreach ($source['games'] as $related) {
                if (count($games) >= self::MAX_GAMES) break 2;
                if (isset($games[$related->id])) continue;
                $games[$related->id] = ['game' => $related, 'label' => $source['name']];
            }
        }

        // A source with no games still gets its "View all" link: the browse page lists every
        // game in it, not only the ranked 7+ ones shown here.
        $links = array_map(fn ($source) => [
            'name' => $source['name'],
            'route' => $source['route'],
            'params' => $source['params'],
        ], $sources);

        // Games from one source keep the old single-section look ("More games in X", no labels),
        // which is most of the catalogue.
        $labels = array_unique(array_column($games, 'label'));

        return [
            'games' => array_values($games),
            'sources' => $links,
            'mixed' => count($labels) > 1,
            'heading' => count($labels) > 1 ? 'More like this' : 'More games in '.reset($labels),
        ];
    }
}
