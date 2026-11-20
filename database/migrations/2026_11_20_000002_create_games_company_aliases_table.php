<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGamesCompanyAliasesTable extends Migration
{
    // The publisher name mappings that were hard-coded in NintendoPageFetcher: store format => DB name
    private const OLD_PUBLISHER_NAME_MAP = [
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

    public function up()
    {
        Schema::create('games_company_aliases', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('games_company_id');
            // Another name the company appears under, e.g. on the Nintendo store
            $table->string('name', 150)->unique();
            $table->timestamps();

            $table->foreign('games_company_id')->references('id')->on('games_companies')->cascadeOnDelete();
        });

        // Skipped: a target that doesn't exist, or an alias that already matches a company's own
        // name (the name match comes first, so the alias would never be used)
        $now = now();
        foreach (self::OLD_PUBLISHER_NAME_MAP as $alias => $target) {
            $companyId = DB::table('games_companies')->whereRaw('LOWER(name) = ?', [mb_strtolower($target)])->value('id');
            $clash = DB::table('games_companies')->whereRaw('LOWER(name) = ?', [mb_strtolower($alias)])->exists();
            if ($companyId && !$clash) {
                DB::table('games_company_aliases')->insert([
                    'games_company_id' => $companyId,
                    'name' => $alias,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('games_company_aliases');
    }
}
