<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default storage location for newly ingested packshots
    |--------------------------------------------------------------------------
    |
    | Where PackshotWriter saves images that ingestion has just downloaded.
    |
    |   'legacy' -> public/img/ps-{square,header}/, no game_images row. The
    |               behaviour that predates object storage.
    |   'spaces' -> the `packshots` disk (Spaces in prod, MinIO on localdev),
    |               plus a game_images row with location = spaces.
    |
    | This governs WRITES only. Reads are always resolved by ImageResolver from
    | whatever game_images says per game, so flipping this never orphans or
    | hides images that are already stored - it only changes where the next
    | download lands. Games written under the other setting keep resolving
    | correctly either way.
    |
    | Kept at 'legacy' through the bulk backfill so new downloads and the
    | backfill could not race. The backfill completed 2026-07-20, so 'spaces'
    | is now safe to set.
    |
    */

    'default_location' => env('PACKSHOTS_DEFAULT_LOCATION', 'legacy'),

];
