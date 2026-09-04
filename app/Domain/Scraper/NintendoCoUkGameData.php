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

    /**
     * @var string Raw HTML, kept for the inline JS config which is not in the DOM tree
     */
    private $html;

    public function __construct(string $html)
    {
        $this->html = $html;
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
        $this->parseHeaderImageUrl();
        $this->parsePublisher();
        $this->parseDescription();
        $this->parseBodyDescription();
        $this->parseStoreConfig();
    }

    /**
     * Parse the inline JS config the store page carries (`nt_data` and `nsuids`).
     *
     * The weekly batch gets title, genres and release date from the pasted listing, so
     * nothing needed these before. Adding a game from its URL alone has no listing to
     * read, so they come from here instead (#135).
     *
     * Price is deliberately absent: the store loads it client-side from the eShop API,
     * so it is not in the HTML at all and has to be entered by hand.
     */
    private function parseStoreConfig(): void
    {
        if (preg_match('/genres:\s*"([^"]*)"/', $this->html, $m)) {
            // Pipe-separated on the page ("platformer|adventure"), comma-separated
            // everywhere else in the app.
            $genres = trim($m[1]);
            $this->parsedData['genres'] = $genres === ''
                ? null
                : implode(', ', array_map('trim', explode('|', $genres)));
        }

        if (preg_match('/gameTitle:\s*"([^"]*)"/', $this->html, $m)) {
            $title = trim($m[1]);
            $this->parsedData['store_title'] = $title === '' ? null : $title;
        }

        if (preg_match('/nsuid:\s*"(\d+)"/', $this->html, $m)) {
            $this->parsedData['nsuid'] = $m[1];
        }

        // Release date is dd/mm/yyyy on the page
        if (preg_match('#releaseDate:\s*"(\d{2})/(\d{2})/(\d{4})"#', $this->html, $m)) {
            $this->parsedData['release_date'] = "{$m[3]}-{$m[2]}-{$m[1]}";
        }
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
     * Parse the Publisher field.
     */
    private function parsePublisher(): void
    {
        $publisher = $this->getGameInfoText('Publisher');
        if ($publisher) {
            $this->parsedData['publisher'] = trim($publisher);
        }
    }

    /**
     * Parse page description from meta description tag.
     */
    private function parseDescription(): void
    {
        try {
            $metaNode = $this->domCrawler->filterXPath('//meta[@name="description"]');
            if ($metaNode->count() > 0) {
                $content = $metaNode->attr('content');
                if ($content) {
                    $this->parsedData['description'] = trim($content);
                }
            }
        } catch (\Exception $e) {
            // Silently handle parsing errors
        }
    }

    /**
     * Parse the header image URL from og:image meta tag.
     */
    private function parseHeaderImageUrl(): void
    {
        try {
            $metaNode = $this->domCrawler->filterXPath('//meta[@property="og:image"]');
            if ($metaNode->count() > 0) {
                $headerUrl = $metaNode->attr('content');
                if ($headerUrl) {
                    $headerUrl = str_replace("\n", "", $headerUrl);
                    $this->parsedData['header_image_url'] = trim($headerUrl);
                }
            }
        } catch (\Exception $e) {
            // Silently handle parsing errors
        }
    }

    /**
     * Parse the body description from the game overview section.
     * Tries several selectors in order; uses the first that yields substantial text.
     * Strips the "Nintendo Switch 2 Edition features" box and the publisher disclaimer.
     */
    private function parseBodyDescription(): void
    {
        // Selectors tried in order — update if Nintendo changes their HTML structure
        $selectors = [
            '[data-section-type="overview"] .content',  // Nintendo UK current structure
            '#Overview .content',                        // Nintendo UK alternative
            '[itemprop="description"]',
            '.body-text',
            '.body-text__wrapper',
            '.overview__description',
            '.game-description',
        ];

        foreach ($selectors as $selector) {
            try {
                $node = $this->domCrawler->filter($selector);
                if ($node->count() === 0) continue;

                $text = trim($node->first()->text());
                if (strlen($text) < 100) continue;

                $cleaned = $this->cleanBodyDescription($text);
                if (strlen($cleaned) > 50) {
                    $this->parsedData['body_description'] = $cleaned;
                    return;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    private function cleanBodyDescription(string $text): string
    {
        // Remove "Nintendo Switch 2 Edition features" box — appears at the top when present.
        // It ends before the main description paragraph begins.
        $text = preg_replace(
            '/Nintendo Switch\s+2\s+Edition\s+features.*?\n\n(?=\S)/si',
            '',
            $text
        );

        // Remove everything from the publisher disclaimer onwards
        $cutoff = 'This description was provided by the publisher.';
        $pos = strpos($text, $cutoff);
        if ($pos !== false) {
            $text = substr($text, 0, $pos);
        }

        // Collapse excessive blank lines and trim
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
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
     * Title as the store itself names the game, from the inline config.
     */
    public function getStoreTitle(): ?string
    {
        return $this->parsedData['store_title'] ?? null;
    }

    /**
     * Nintendo genres, comma-separated to match how the rest of the app stores them.
     */
    public function getGenres(): ?string
    {
        return $this->parsedData['genres'] ?? null;
    }

    /**
     * Release date as Y-m-d.
     */
    public function getReleaseDate(): ?string
    {
        return $this->parsedData['release_date'] ?? null;
    }

    public function getNsuid(): ?string
    {
        return $this->parsedData['nsuid'] ?? null;
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
     * Get header image URL.
     */
    public function getHeaderImageUrl(): ?string
    {
        return $this->parsedData['header_image_url'] ?? null;
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

    public function getPublisher(): ?string
    {
        return $this->parsedData['publisher'] ?? null;
    }

    public function getDescription(): ?string
    {
        return $this->parsedData['description'] ?? null;
    }

    public function getBodyDescription(): ?string
    {
        return $this->parsedData['body_description'] ?? null;
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
