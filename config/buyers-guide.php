<?php

/*
 * Buyer's guide content: /buyers-guide/us and /buyers-guide/uk.
 *
 * The page stays at the same URL from year to year, so updating it means editing this file:
 * change the year, the updated date and note, the hardware and the game picks.
 *
 * Hardware is listed by Amazon product code (ASIN) for each region. An item with no code for a
 * region is left off that region's page; UK codes are found by hand, as the Creators API only
 * covers Amazon US.
 *
 * Games are listed by game ID, in the order they appear on the page, and both regions share the
 * same picks. Each game's buy link comes from its own Amazon codes, so a game with no UK code
 * shows on the UK page without a buy button.
 */

return [

    // Shown in the page title: "Nintendo Switch buyer's guide 2026 (US)"
    'year' => '2026',

    // Shown under the heading: "Updated 7 November 2026 for Black Friday and the holidays."
    // Change the note with the season, e.g. leave it empty after Christmas.
    'updated' => '2026-11-07',
    'updated_note' => 'for Black Friday and the holidays',

    'hardware' => [
        [
            'title' => 'Nintendo Switch 2',
            'note' => 'The console on its own.',
            'asin_us' => 'B0F3GWXLTS',
            'asin_uk' => 'B0F2T4JFVG',
        ],
        [
            'title' => 'Nintendo Switch 2 + Mario Kart World bundle',
            'note' => 'The console with Mario Kart World.',
            'asin_us' => 'B0HGHB3V16',
            'asin_uk' => 'B0HLP9TSYK',
        ],
        [
            'title' => 'Nintendo Switch 2 + Donkey Kong Bananza bundle',
            'note' => 'The console with Donkey Kong Bananza.',
            'asin_us' => 'B0FY3PT325',
            'asin_uk' => 'B0HC4PMX7L',
        ],
        [
            'title' => 'Nintendo Switch 2 Pro Controller',
            'note' => 'A full-size controller for playing on the TV.',
            'asin_us' => 'B0F3VTFD6J',
            'asin_uk' => null,
        ],
        [
            // UK only: no Switch 2 Pro Controller found on Amazon UK (7 Nov 2026)
            'title' => 'Nintendo Switch Pro Controller',
            'note' => 'A full-size controller for playing on the TV. Works with Switch 2 as well as Switch.',
            'asin_us' => null,
            'asin_uk' => 'B01N4ND1T2',
        ],
        [
            'title' => 'Joy-Con 2 pair',
            'note' => 'A second set of Joy-Con 2 controllers, for playing with friends.',
            'asin_us' => 'B0GFQ25QWY',
            'asin_uk' => 'B0F3JM95S4',
        ],
        [
            'title' => 'SanDisk microSD Express card for Nintendo Switch 2, 256GB',
            'note' => 'More storage. Switch 2 can only store games on a microSD Express card, not a standard microSD card.',
            'asin_us' => 'B0F3P3X5P2',
            'asin_uk' => 'B0F3P3X5P2',
        ],
        [
            'title' => 'SanDisk microSD Express card, 512GB',
            'note' => 'Double the storage. Works with Switch 2.',
            'asin_us' => null,
            'asin_uk' => 'B0DTQ35NH6',
        ],
        [
            'title' => 'Nintendo Switch 2 Camera',
            'note' => 'For GameChat video and camera games.',
            'asin_us' => 'B0DWZY7NKN',
            'asin_uk' => 'B0F3JNJXQ5',
        ],
        [
            'title' => 'Piranha Plant Camera',
            'note' => 'A Super Mario-themed camera for Switch 2.',
            'asin_us' => 'B0F59VYVMD',
            'asin_uk' => 'B0F3P1XSSF',
        ],
        [
            'title' => 'Nintendo Switch 2 Carrying Case & Screen Protector',
            'note' => 'Nintendo\'s own case, with a screen protector.',
            'asin_us' => 'B0F3JT1NT8',
            'asin_uk' => 'B0F3JT1NT8',
        ],
        [
            'title' => 'Orzly carrying case',
            'note' => 'A padded case for Switch 2, with screen padding.',
            'asin_us' => 'B0F49DHSHJ',
            'asin_uk' => 'B0F49DHSHJ',
        ],
    ],

    // First draft: the top 15 ranked games on each console with an Amazon US code (7 Nov 2026).
    'games' => [
        'switch-2' => [
            17705, // Sektori
            17821, // Mina the Hollower: Nintendo Switch 2 Edition
            17197, // Resident Evil Requiem
            18193, // Lies of P: Complete Edition
            17245, // Pokémon Pokopia
            18577, // Resident Evil 2 Deluxe Edition
            16955, // Final Fantasy VII Remake Intergrade
            15013, // Cyberpunk 2077: Ultimate Edition
            15019, // The Legend of Zelda: Breath of the Wild: Nintendo Switch 2 Edition
            16811, // Sonic Racing: CrossWorlds - Nintendo Switch 2 Edition
            15011, // Mario Kart World
            18294, // Elden Ring Tarnished Edition
            18407, // Fire Emblem: Fortune's Weave
            15015, // Split Fiction
            17280, // Blue Prince
        ],
        'switch-1' => [
            1,     // The Legend of Zelda: Breath of the Wild
            66,    // Super Mario Odyssey
            9416,  // Metroid Prime Remastered
            74,    // Celeste
            4496,  // Hades
            9452,  // The Legend of Zelda: Tears of the Kingdom
            8560,  // Persona 5 Royal
            7740,  // Xenoblade Chronicles 3
            6696,  // Tetris Effect: Connected
            6679,  // Dusk
            4610,  // Monster Hunter Rise
            927,   // Dead Cells
            40,    // Mario Kart 8 Deluxe
            67,    // Hollow Knight
            11947, // Unicorn Overlord
        ],
    ],

];
