<?php

namespace App\Domain\DataSource\NintendoCoUk;

/**
 * Pulls NSUIDs out of a Nintendo.co.uk search payload.
 *
 * Shared by the fetch command (which needs every NSUID in the catalogue) and the
 * parser (which needs one record's NSUIDs to look up its price). One helper rather
 * than two, so the set of ids that gets fetched cannot drift from the set that gets
 * looked up - a drift that would show as "the price API had no answer" for records
 * whose price had in fact been fetched perfectly well.
 */
class NsuidExtractor
{
    /**
     * @param  array|null  $rawJsonData  the decoded source_data_json
     * @return string[]
     */
    public function extract(?array $rawJsonData): array
    {
        if (empty($rawJsonData['nsuid_txt'])) {
            return [];
        }

        $nsuids = $rawJsonData['nsuid_txt'];

        // Observed as an array of strings, but a single-element field arriving as a
        // bare string is exactly the shape difference that bit the review-feed
        // importer (array vs object parse mode), so it is handled rather than assumed.
        if (!is_array($nsuids)) {
            $nsuids = [$nsuids];
        }

        $clean = [];

        foreach ($nsuids as $nsuid) {
            $nsuid = trim((string) $nsuid);

            if ($nsuid === '') {
                continue;
            }

            $clean[] = $nsuid;
        }

        return array_values(array_unique($clean));
    }

    public function extractFromJson(?string $json): array
    {
        if (empty($json)) {
            return [];
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return [];
        }

        return $this->extract($decoded);
    }
}
