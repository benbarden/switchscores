<?php

namespace App\Domain\Scraper;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class NintendoCoUkGameData
{
    /**
     * @var DomCrawler
     */
    private $domCrawler;

    /**
     * @var array
     */
    private $parsedData = [];

    public function __construct(string $html)
    {
        $this->domCrawler = new DomCrawler($html);
        $this->parse();
    }

    /**
     * Parse all game data from the HTML.
     */
    private function parse(): void
    {
        $this->parsePlayersField();
        $this->parseMultiplayerMode();
        $this->parseFeatures();
    }

    /**
     * Parse the Players field.
     * Example: "Single System (1), Local Wireless (1-8), Online (1-8)"
     */
    private function parsePlayersField(): void
    {
        $playersText = $this->getGameInfoText('Players');
        if (!$playersText) {
            return;
        }

        // Parse each component
        // Single System (1) or Single System (2-4)
        if (preg_match('/Single System\s*\(([^)]+)\)/i', $playersText, $matches)) {
            $this->parsedData['players_local'] = trim($matches[1]);
        }

        // Local Wireless (1-8)
        if (preg_match('/Local Wireless\s*\(([^)]+)\)/i', $playersText, $matches)) {
            $this->parsedData['players_wireless'] = trim($matches[1]);
        }

        // Online (1-8)
        if (preg_match('/Online\s*\(([^)]+)\)/i', $playersText, $matches)) {
            $this->parsedData['players_online'] = trim($matches[1]);
        }
    }

    /**
     * Parse the Multiplayer mode field.
     * Example: "Simultaneous"
     */
    private function parseMultiplayerMode(): void
    {
        $modeText = $this->getGameInfoText('Multiplayer mode');
        if ($modeText) {
            $this->parsedData['multiplayer_mode'] = trim($modeText);
        }
    }

    /**
     * Parse the Features field.
     * Extracts feature names from the anchor title attributes.
     */
    private function parseFeatures(): void
    {
        $features = [];

        try {
            // Find the Features section
            $this->domCrawler->filter('p.game_info_title')->each(function (DomCrawler $node) use (&$features) {
                if (trim($node->text()) === 'Features') {
                    // Get the next sibling with class game_info_text
                    $parent = $node->ancestors()->first();
                    $featuresNode = $parent->filter('p.game_info_text.features');

                    if ($featuresNode->count() > 0) {
                        $featuresNode->filter('a')->each(function (DomCrawler $link) use (&$features) {
                            $title = $link->attr('title');
                            if ($title) {
                                $features[] = $title;
                            }
                        });
                    }
                }
            });
        } catch (\Exception $e) {
            // Silently handle parsing errors
        }

        if (!empty($features)) {
            $this->parsedData['features'] = $features;
        }
    }

    /**
     * Get the text content for a game_info_title field.
     */
    private function getGameInfoText(string $titleName): ?string
    {
        $result = null;

        try {
            $this->domCrawler->filter('p.game_info_title')->each(function (DomCrawler $node) use ($titleName, &$result) {
                if (trim($node->text()) === $titleName) {
                    // Get the parent container and find the game_info_text
                    $parent = $node->ancestors()->first();
                    $textNode = $parent->filter('p.game_info_text');

                    if ($textNode->count() > 0) {
                        $result = trim($textNode->first()->text());
                    }
                }
            });
        } catch (\Exception $e) {
            // Silently handle parsing errors
        }

        return $result;
    }

    /**
     * Get all parsed data.
     */
    public function getData(): array
    {
        return $this->parsedData;
    }

    /**
     * Get players local value.
     */
    public function getPlayersLocal(): ?string
    {
        return $this->parsedData['players_local'] ?? null;
    }

    /**
     * Get players wireless value.
     */
    public function getPlayersWireless(): ?string
    {
        return $this->parsedData['players_wireless'] ?? null;
    }

    /**
     * Get players online value.
     */
    public function getPlayersOnline(): ?string
    {
        return $this->parsedData['players_online'] ?? null;
    }

    /**
     * Get multiplayer mode value.
     */
    public function getMultiplayerMode(): ?string
    {
        return $this->parsedData['multiplayer_mode'] ?? null;
    }

    /**
     * Get features array.
     */
    public function getFeatures(): array
    {
        return $this->parsedData['features'] ?? [];
    }

    /**
     * Check if a specific feature is present.
     */
    public function hasFeature(string $featureName): bool
    {
        $features = $this->getFeatures();
        return in_array($featureName, $features, true);
    }

    /**
     * Check if online play is available.
     */
    public function hasOnlinePlay(): bool
    {
        // Check features for "Online play" or "Online Play"
        $features = $this->getFeatures();
        foreach ($features as $feature) {
            if (stripos($feature, 'online play') !== false) {
                return true;
            }
        }

        // Also true if online players are specified
        return $this->getPlayersOnline() !== null;
    }

    /**
     * Check if local multiplayer is available.
     */
    public function hasLocalMultiplayer(): bool
    {
        // Check features
        $features = $this->getFeatures();
        foreach ($features as $feature) {
            if (stripos($feature, 'local multiplayer') !== false) {
                return true;
            }
        }

        // Also check if local players > 1 or wireless players exist
        $local = $this->getPlayersLocal();
        if ($local !== null && $local !== '1') {
            return true;
        }

        return $this->getPlayersWireless() !== null;
    }

    /**
     * Check if TV mode is supported.
     */
    public function hasPlayModeTv(): bool
    {
        return $this->hasFeature('TV mode');
    }

    /**
     * Check if tabletop mode is supported.
     */
    public function hasPlayModeTabletop(): bool
    {
        return $this->hasFeature('Tabletop mode');
    }

    /**
     * Check if handheld mode is supported.
     */
    public function hasPlayModeHandheld(): bool
    {
        return $this->hasFeature('Handheld mode');
    }

    /**
     * Check if any player/multiplayer data was found.
     */
    public function hasData(): bool
    {
        return !empty($this->parsedData);
    }

    /**
     * Check if any player count data was found.
     */
    public function hasPlayerData(): bool
    {
        return isset($this->parsedData['players_local'])
            || isset($this->parsedData['players_wireless'])
            || isset($this->parsedData['players_online']);
    }

    /**
     * Get combined players value for the games.players field.
     * Returns "1" for single player, or "1-8" for multiplayer range.
     */
    public function getCombinedPlayers(): ?string
    {
        if (!$this->hasPlayerData()) {
            return null;
        }

        $allMins = [];
        $allMaxs = [];

        // Parse each player field
        $fields = [
            $this->parsedData['players_local'] ?? null,
            $this->parsedData['players_wireless'] ?? null,
            $this->parsedData['players_online'] ?? null,
        ];

        foreach ($fields as $field) {
            if ($field === null) {
                continue;
            }

            list($min, $max) = $this->parsePlayerRange($field);
            if ($min !== null) {
                $allMins[] = $min;
            }
            if ($max !== null) {
                $allMaxs[] = $max;
            }
        }

        if (empty($allMins) || empty($allMaxs)) {
            return null;
        }

        $overallMin = min($allMins);
        $overallMax = max($allMaxs);

        if ($overallMin === $overallMax) {
            return (string) $overallMin;
        }

        return "{$overallMin}-{$overallMax}";
    }

    /**
     * Parse a player range string like "1" or "2-4" into [min, max].
     */
    private function parsePlayerRange(string $value): array
    {
        $value = trim($value);

        // Range format: "1-8" or "2-4"
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $value, $matches)) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        // Single value: "1" or "4"
        if (preg_match('/^(\d+)$/', $value, $matches)) {
            $num = (int) $matches[1];
            return [$num, $num];
        }

        return [null, null];
    }
}
