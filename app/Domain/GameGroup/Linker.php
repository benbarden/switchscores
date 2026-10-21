<?php

namespace App\Domain\GameGroup;

use App\Models\Game;
use App\Models\GameGroup;

/**
 * Links two games as the same game on different consoles (#44), creating a group or
 * joining one that already exists. A group holds at most one game per console.
 */
class Linker
{
    public function __construct(
        private Repository $repoGameGroup,
        private TitleMatchKey $titleMatchKey
    )
    {
    }

    /**
     * @throws LinkException when the two games can't be linked; the message says why.
     */
    public function link(Game $game, Game $other, string $matchType): GameGroup
    {
        if (!in_array($matchType, [GameGroup::MATCH_IDENTICAL, GameGroup::MATCH_ENHANCED], true)) {
            throw new LinkException('Unknown match type: '.$matchType);
        }
        if ($game->id == $other->id) {
            throw new LinkException('A game can\'t be linked to itself.');
        }
        if ($game->console_id == $other->console_id) {
            throw new LinkException($other->title.' is on the same console.');
        }

        $group = $game->gameGroup;
        $otherGroup = $other->gameGroup;

        if ($group && $otherGroup) {
            if ($group->id == $otherGroup->id) {
                return $group;
            }
            throw new LinkException('Both games are already in different groups. Unlink one of them first.');
        }

        if (!$group && !$otherGroup) {
            $group = $this->repoGameGroup->create($this->groupName($game, $other), $matchType);
            $this->repoGameGroup->linkGame($group, $game);
            $this->repoGameGroup->linkGame($group, $other);
            return $group;
        }

        // One of them is already grouped: the other joins that group, keeping its match type
        [$group, $joining] = $group ? [$group, $other] : [$otherGroup, $game];
        foreach ($group->games as $member) {
            if ($member->console_id == $joining->console_id) {
                throw new LinkException($group->name.' already has a game on that console: '.$member->title.'.');
            }
        }
        $this->repoGameGroup->linkGame($group, $joining);

        return $group;
    }

    /**
     * Named after the Switch 1 title, without any console suffix - the shared name of the game.
     */
    private function groupName(Game $game, Game $other): string
    {
        $base = $game->console_id < $other->console_id ? $game : $other;
        return $this->titleMatchKey->stripConsoleSuffix($base->title);
    }
}
