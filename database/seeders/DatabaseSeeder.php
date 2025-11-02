<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        DB::table('news_post_types')->upsert([
            ['slug' => 'almost_ranked', 'title' => 'Games that need 1 more review', 'cadence_days' => 14, 'is_enabled' => true],
            ['slug' => 'newly_ranked', 'title' => 'Newly ranked games', 'cadence_days' => 30, 'is_enabled' => true],
            ['slug' => 'forgotten_gem', 'title' => 'Forgotten gems worth a look', 'cadence_days' => 21, 'is_enabled' => true],
        ], ['slug'], ['title', 'cadence_days', 'is_enabled']);
        */
    }
}
