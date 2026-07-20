<?php

namespace App\Domain\Game;

use App\Models\GameImage;
use Illuminate\Database\Query\Builder;

/**
 * Lets raw-row queries resolve packshots from object storage.
 *
 * ImageResolver reads `game_images` through the Eloquent `images` relation, which raw
 * `DB::table('games')` rows do not have — so before this existed, every list built from a raw
 * query fell through to the legacy path unconditionally, no matter what `game_images` said.
 * Harmless while legacy files exist; broken images the moment ingestion writes only to object
 * storage, or the Phase 2 legacy delete runs.
 *
 * apply() adds the join and the aliased columns; hydrate() turns those columns back into a
 * GameImage. The two live together so the read and write sides cannot disagree about column
 * names — the same reasoning as ImageResolver::storageKey() being shared with the migrator.
 *
 * Aliases are prefixed to avoid colliding with columns the callers already select: Tag\Repository
 * selects `games.id AS game_id`, which a bare `game_id` alias here would silently overwrite.
 */
class PackshotJoin
{
    const ALIAS_LOCATION = 'packshot_location';
    const ALIAS_SQUARE_FILENAME = 'packshot_square_filename';
    const ALIAS_HEADER_FILENAME = 'packshot_header_filename';
    const ALIAS_SQUARE_UPDATED_AT = 'packshot_square_updated_at';
    const ALIAS_HEADER_UPDATED_AT = 'packshot_header_updated_at';

    /**
     * Left-join game_images and select its columns under prefixed aliases.
     *
     * Left, not inner: a game with no game_images row must still be returned, and simply
     * resolves via the legacy path as before.
     */
    public static function apply(Builder $query): Builder
    {
        return $query
            ->leftJoin('game_images', 'games.id', '=', 'game_images.game_id')
            ->addSelect([
                'game_images.location AS ' . self::ALIAS_LOCATION,
                'game_images.square_filename AS ' . self::ALIAS_SQUARE_FILENAME,
                'game_images.header_filename AS ' . self::ALIAS_HEADER_FILENAME,
                'game_images.square_updated_at AS ' . self::ALIAS_SQUARE_UPDATED_AT,
                'game_images.header_updated_at AS ' . self::ALIAS_HEADER_UPDATED_AT,
            ]);
    }

    /**
     * Rebuild a GameImage from the aliased columns on a raw row, or null when the row carries
     * no packshot record (no join applied, or no matching game_images row).
     *
     * newFromBuilder() is used rather than a constructor so the model's datetime casts apply,
     * which is what lets ImageResolver::spacesUrl() call ->timestamp on the updated_at values.
     */
    public static function hydrate($row): ?GameImage
    {
        if (!is_object($row) || !isset($row->{self::ALIAS_LOCATION})) {
            return null;
        }

        return (new GameImage)->newFromBuilder([
            'location'          => $row->{self::ALIAS_LOCATION},
            'square_filename'   => $row->{self::ALIAS_SQUARE_FILENAME} ?? null,
            'header_filename'   => $row->{self::ALIAS_HEADER_FILENAME} ?? null,
            'square_updated_at' => $row->{self::ALIAS_SQUARE_UPDATED_AT} ?? null,
            'header_updated_at' => $row->{self::ALIAS_HEADER_UPDATED_AT} ?? null,
        ]);
    }
}
