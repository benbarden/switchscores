<?php

namespace App\Domain\WeeklyBatch;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\Scraper\NintendoCoUkGameData;

class NintendoPageFetcher
{
    // Games by the same publisher threshold — below this count, flag for review
    private const NEW_PUBLISHER_GAME_THRESHOLD = 3;

    // Publisher name mappings: Nintendo site format → DB name
    private const PUBLISHER_NAME_MAP = [
        'Bandai Namco'              => 'Bandai Namco Entertainment',
        'Bethesda'                  => 'Bethesda Softworks',
        'Brain Seal Entertainment'  => 'Brain Seal',
        'D3PUBLISHER'               => 'D3 Publisher',
        'Dolores Ent.'              => 'Dolores Entertainment',
        'Dolores Entertainment SL'  => 'Dolores Entertainment',
        'Fáyer'                     => 'Fayer',
        'LAN - GAMES EOOD'          => 'Lan Games',
        'Reddeer.Games'             => 'RedDeerGames',
        'Sanuk'                     => 'Sanuk Games',
        'SERIALGAMES'               => 'Serial Games',
        'Terarin Games'             => 'Terarin',
        'Koei Tecmo Europe'         => 'Koei Tecmo',
        'CRAFTS&MEISTER'            => 'Crafts & Meister',
        'TREVA'                     => 'Treva Entertainment',
        'Anxious Noob Games Ltd'    => 'Anxious Noob Games',
        'Trefl S.A'                 => 'Trefl SA',
        'DreadXP'                   => 'Dread XP',
        'LocaGames'                 => 'Loca Games',
        'ODDCADIA'                  => 'Oddcadia',
        'RiverseGames'              => 'Rioverse Games',
        'Take IT Studio!'           => 'Take IT Studio',
        'Telltale'                  => 'Telltale Games',
    ];

    public function __construct(
        private GamesCompanyRepository $repoCompany
    ) {}

    /**
     * Fetch a Nintendo game page and return structured data with LQ determination.
     *
     * Returns:
     *   publisher_raw        — as seen on the Nintendo page
     *   publisher_normalised — after name mapping applied
     *   players              — combined player count string e.g. "1-4"
     *   description          — from meta description
     *   lq_confirmed         — publisher found in DB with is_low_quality = 1
     *   lq_uncertain         — publisher not found in DB, or found with very few games
     *   lq_flag_reason       — human-readable reason for any flag
     *   lq_publisher_name    — publisher name that triggered the flag (if any)
     */
    public function fetch(string $url): array
    {
        $httpClient = HttpClient::create(['timeout' => 30]);
        $browser = new HttpBrowser($httpClient);
        $browser->setMaxRedirects(3);

        $crawler = $browser->request('GET', $url);
        $statusCode = $browser->getResponse()->getStatusCode();

        if ($statusCode !== 200) {
            throw new \RuntimeException("HTTP {$statusCode} fetching: {$url}");
        }

        $html = $crawler->html();
        $scraper = new NintendoCoUkGameData($html);

        $publisherRaw        = $scraper->getPublisher();
        $publisherNormalised = $this->normalisePublisherName($publisherRaw);
        $players             = $scraper->getCombinedPlayers();
        $description         = $scraper->getBodyDescription() ?? $scraper->getDescription();

        [$lqConfirmed, $lqUncertain, $lqFlagReason, $lqPublisherName, $canonicalName] =
            $this->checkPublisherLq($publisherRaw, $publisherNormalised);

        // Prefer the DB canonical name when the publisher was found in the database
        if ($canonicalName !== null) {
            $publisherNormalised = $canonicalName;
        }

        return [
            'publisher_raw'        => $publisherRaw,
            'publisher_normalised' => $publisherNormalised,
            'players'              => $players,
            'description'          => $description,
            'lq_confirmed'         => $lqConfirmed,
            'lq_uncertain'         => $lqUncertain,
            'lq_flag_reason'       => $lqFlagReason,
            'lq_publisher_name'    => $lqPublisherName,
        ];
    }

    private function normalisePublisherName(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $raw = trim($raw);

        // Apply explicit name mapping first
        if (isset(self::PUBLISHER_NAME_MAP[$raw])) {
            return self::PUBLISHER_NAME_MAP[$raw];
        }

        // ALL CAPS publisher not in map → convert to Title Case (COLOPL → Colopl)
        $letters = preg_replace('/[^a-zA-Z]/', '', $raw);
        if ($letters !== '' && $letters === strtoupper($letters)) {
            return ucwords(strtolower($raw));
        }

        return $raw;
    }

    /**
     * Check whether the publisher is confirmed LQ, uncertain, or fine.
     * Returns [$lqConfirmed, $lqUncertain, $lqFlagReason, $lqPublisherName, $canonicalName].
     * $canonicalName is the DB-authoritative name when the publisher was found; null otherwise.
     */
    private function checkPublisherLq(?string $publisherRaw, ?string $publisherNormalised): array
    {
        if ($publisherNormalised === null) {
            return [false, true, 'Publisher not found on Nintendo page', null, null];
        }

        $company = $this->repoCompany->findByNameCaseInsensitive($publisherNormalised);

        if ($company === null) {
            return [false, true, "Publisher not in DB: {$publisherNormalised}", $publisherNormalised, null];
        }

        if ($company->is_low_quality) {
            return [true, false, "Publisher confirmed LQ: {$company->name}", $company->name, $company->name];
        }

        // Publisher found and not LQ — check if they have very few games (emerging publisher)
        $gameCount = $company->publisher_games_count ?? 0;
        if ($gameCount < self::NEW_PUBLISHER_GAME_THRESHOLD) {
            $gameWord = $gameCount === 1 ? 'game' : 'games';
            $countDesc = $gameCount === 0 ? 'no games' : "{$gameCount} {$gameWord}";
            return [false, true, "New or unverified publisher ({$countDesc} in DB): {$company->name}", $company->name, $company->name];
        }

        return [false, false, '', null, $company->name];
    }
}
