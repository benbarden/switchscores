<?php

namespace App\Domain\PartnerFeedLink;

use App\Models\Game;

use App\Domain\GameTitleMatch\MatchRule;
use App\Domain\GameTitleHash\Repository as RepoGameTitleHash;

/**
 * Runs a feed's title match rule against a set of titles and reports what happened,
 * without writing anything.
 *
 * The authoritative result always comes from MatchRule, the same class ParseTitle uses,
 * so the preview cannot drift from what the cron actually does. The extra preg_* calls
 * in here are for diagnosis only - MatchRule returns null for several different problems
 * and we want to tell them apart.
 */
class TestTitleRule
{
    const STATUS_MATCHED_GAME = 'Matched a game';
    const STATUS_NO_GAME = 'Parsed, but no game found';
    const STATUS_RULE_NO_MATCH = 'Rule did not match the title';
    const STATUS_INDEX_MISSING = 'Rule matched, but the capture index does not exist';

    private $matchRulePattern;

    private $matchRuleIndex;

    private $repoGameTitleHash;

    public function __construct()
    {
        $this->repoGameTitleHash = new RepoGameTitleHash();
    }

    public function setRule($matchRulePattern, $matchRuleIndex)
    {
        $this->matchRulePattern = $matchRulePattern;
        $this->matchRuleIndex = $matchRuleIndex;
        return $this;
    }

    /**
     * Checks the pattern itself, before any titles are involved.
     *
     * @return array{valid: bool, error: ?string, warnings: array, prepared: ?string}
     */
    public function validatePattern()
    {
        $result = ['valid' => false, 'error' => null, 'warnings' => [], 'prepared' => null];

        if ($this->matchRulePattern === null || $this->matchRulePattern === '') {
            $result['error'] = 'No match rule pattern set.';
            return $result;
        }

        if ($this->matchRuleIndex === null || $this->matchRuleIndex === '') {
            $result['error'] = 'No match rule index set.';
            return $result;
        }

        // MatchRule wraps the pattern in /^...$/ before use, so validate what it will
        // actually run, not what was typed.
        $matchRule = new MatchRule($this->matchRulePattern, $this->matchRuleIndex);
        $prepared = $matchRule->getPattern();
        $result['prepared'] = $prepared;

        if (@preg_match($prepared, '') === false) {
            $result['error'] = 'Not a valid regular expression: '.preg_last_error_msg();
            return $result;
        }

        $result['valid'] = true;
        $result['warnings'] = $this->findWarnings();

        return $result;
    }

    /**
     * Traps that produce a rule which looks correct but silently never matches.
     */
    private function findWarnings()
    {
        $warnings = [];

        // MatchRule builds the pattern without the /u modifier, so a multibyte character
        // inside a character class is treated as separate single bytes and can never
        // match. e.g. [-–] looks right and matches nothing. Outside a class it is fine.
        if (preg_match_all('/\[[^\]]*\]/', $this->matchRulePattern, $classes)) {
            foreach ($classes[0] as $class) {
                if (strlen($class) !== mb_strlen($class)) {
                    $warnings[] = 'Character class '.$class.' contains a multibyte character. '
                        .'Match rules run without the /u modifier, so this will never match. '
                        .'Use an alternation such as (?:-|–) instead.';
                }
            }
        }

        if (!str_contains($this->matchRulePattern, '(')) {
            $warnings[] = 'The pattern has no capture group, so there is nothing for the '
                .'match rule index to return.';
        }

        return $warnings;
    }

    /**
     * @param array $titles Plain item titles.
     * @return array{results: array, summary: array}
     */
    public function test(array $titles)
    {
        $results = [];

        $summary = [
            self::STATUS_MATCHED_GAME => 0,
            self::STATUS_NO_GAME => 0,
            self::STATUS_RULE_NO_MATCH => 0,
            self::STATUS_INDEX_MISSING => 0,
        ];

        foreach ($titles as $title) {
            $result = $this->testTitle($title);
            $results[] = $result;
            $summary[$result['status']]++;
        }

        $total = count($results);

        return [
            'results' => $results,
            'summary' => $summary,
            'total' => $total,
            'match_rate' => $total > 0 ? round(($summary[self::STATUS_MATCHED_GAME] / $total) * 100) : 0,
        ];
    }

    public function testTitle($title)
    {
        $matchRule = new MatchRule($this->matchRulePattern, $this->matchRuleIndex);
        $titleMatches = $matchRule->generateMatch($title);
        $parsedTitle = $matchRule->getParsedTitle();

        $result = [
            'item_title' => $title,
            'parsed_title' => $parsedTitle,
            'game_id' => null,
            'game_title' => null,
            'status' => self::STATUS_RULE_NO_MATCH,
        ];

        if ($titleMatches === null) {
            // Diagnosis only: work out whether the pattern failed outright, or matched
            // but has no group at the requested index. MatchRule cannot tell us which.
            $matchCount = @preg_match_all($matchRule->getPattern(), $title, $rawMatches);
            if ($matchCount && !array_key_exists($this->matchRuleIndex, $rawMatches)) {
                $result['status'] = self::STATUS_INDEX_MISSING;
            }
            return $result;
        }

        $gameTitleHash = $this->repoGameTitleHash->byTitleGroup($titleMatches);

        if ($gameTitleHash) {
            $game = Game::find($gameTitleHash->game_id);
            $result['game_id'] = $gameTitleHash->game_id;
            $result['game_title'] = $game ? $game->title : null;
            $result['status'] = self::STATUS_MATCHED_GAME;
        } else {
            $result['status'] = self::STATUS_NO_GAME;
        }

        return $result;
    }

    /**
     * Suggests a starting pattern from a set of titles, by treating whatever is common to
     * the start and end of every title as fixed, and the varying middle as the game name.
     *
     * A starting point to edit, not an authoritative answer: it can only find what every
     * sample title shares, so feeds with a varying tail (e.g. "Review: X (Switch) - tagline")
     * will produce something rougher than a hand-written rule.
     *
     * @return array{pattern: ?string, index: int}
     */
    public function suggestRule(array $titles)
    {
        $titles = array_values(array_filter($titles));

        if (count($titles) < 2) {
            return ['pattern' => null, 'index' => 1];
        }

        $prefix = $this->longestCommonPrefix($titles);
        $suffix = $this->longestCommonSuffix($titles, strlen($prefix));

        if ($prefix === '' && $suffix === '') {
            return ['pattern' => null, 'index' => 1];
        }

        $pattern = preg_quote($prefix, '/').'(.*)'.preg_quote($suffix, '/');

        return ['pattern' => $pattern, 'index' => 1];
    }

    private function longestCommonPrefix(array $titles)
    {
        $prefix = array_shift($titles);

        foreach ($titles as $title) {
            while ($prefix !== '' && !str_starts_with($title, $prefix)) {
                $prefix = substr($prefix, 0, -1);
            }
        }

        return $prefix;
    }

    private function longestCommonSuffix(array $titles, $prefixLength)
    {
        $suffix = substr(array_shift($titles), $prefixLength);

        foreach ($titles as $title) {
            // Never let the suffix run back into the prefix, or the two would overlap
            // and the capture group in the middle could match nothing.
            $comparable = substr($title, $prefixLength);
            while ($suffix !== '' && !str_ends_with($comparable, $suffix)) {
                $suffix = substr($suffix, 1);
            }
        }

        return $suffix;
    }
}
