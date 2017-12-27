<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateGameImageCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateGameImageCount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes image count for games.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info(' *** '.$this->signature.' ['.date('Y-m-d H:i:s').']'.' *** ');

        // Review count
        $this->info('Game image counts');

        $imageCountList = \DB::select("
            SELECT g.id AS game_id, g.title, count(gi.game_id) AS image_count
            FROM games g
            LEFT JOIN game_images gi ON g.id = gi.game_id
            GROUP BY g.id;
        ");

        $this->info('Updating '.count($imageCountList).' games');

        foreach ($imageCountList as $item) {
            $gameId = $item->game_id;
            $imageCount = $item->image_count;
            \DB::update("
                UPDATE games SET image_count = ? WHERE id = ?
            ", array($imageCount, $gameId));
        }

    }
}
