<?php

namespace App\Console\Commands\Adhoc;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;
use App\Factories\GamesCompanyFactory;

use App\Services\Game\Images as GameImages;

class CleanUpOldPackshots extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AdhocCleanUpOldPackshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adhoc job';

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
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        // Get all games where we can safely migrate the images
        $gamesList = DB::select("
            SELECT * FROM games
            WHERE (boxart_square_url IS NOT NULL AND image_square IS NOT NULL)
            OR (boxart_header_image IS NOT NULL AND image_header IS NOT NULL)
        ");

        foreach ($gamesList as $gameDbItem) {

            $gameId = $gameDbItem->id;

            $game = $this->getServiceGame()->find($gameId);
            if (!$game) {
                $logger->error('Cannot load game: '.$gameId);
                continue;
            }

            $gameTitle = $game->title;

            // Old images
            $boxartSquareUrl = $game->boxart_square_url;
            $boxartHeaderImage = $game->boxart_header_image;
            $oldSquarePath = public_path().'/img/games/square/'.$boxartSquareUrl;
            $oldHeaderPath = public_path().'/img/games/header/'.$boxartHeaderImage;

            // New images
            $imageSquare = $game->image_square;
            $imageHeader = $game->image_header;
            $newSquarePath = public_path().GameImages::PATH_IMAGE_SQUARE.$imageSquare;
            $newHeaderPath = public_path().GameImages::PATH_IMAGE_HEADER.$imageHeader;

            // Square images
            if ($boxartSquareUrl && $imageSquare) {

                if (file_exists($oldSquarePath)) {
                    $logger->info('Removing old square image: '.$oldSquarePath);
                    unlink($oldSquarePath);
                    $game->boxart_square_url = null;

                }

            }

            // Header images
            if ($boxartHeaderImage && $imageHeader) {

                if (file_exists($oldHeaderPath)) {
                    $logger->info('Removing old header image: '.$oldHeaderPath);
                    unlink($oldHeaderPath);
                    $game->boxart_header_image = null;
                }

            }

            $game->save();

        }

        $logger->info('Complete');
    }
}
