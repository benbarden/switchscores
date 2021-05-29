<?php

namespace App\Console\Commands\ImageGen;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;

use Intervention\Image\Facades\Image;

class SeriesImage extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'IGSeriesImage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an image for a series';

    private $repoGameLists;
    private $repoGameSeries;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        GameListsRepository $repoGameLists,
        GameSeriesRepository $repoGameSeries
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->repoGameSeries = $repoGameSeries;
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

        $seriesList = $this->repoGameSeries->getAll();

        foreach ($seriesList as $series) {

            $gamesWithSeries = $this->repoGameLists->bySeriesWithImages($series, 3);

            $seriesName = $series->series;
            $seriesFilename = $series->link_title.'.jpg';

            // Make a blank canvas to put the images onto.
            $imageWidth = 400;
            $img = Image::canvas($imageWidth, 200, '#ccc');

            if (count($gamesWithSeries) == 0) {
                // Just save the blank image and go to the next record
                $logger->info('No games for series '.$seriesName.'; saving blank image');
                $img->save(public_path('img/gen/series/'.$seriesFilename));
                $series->landing_image = $seriesFilename;
                $series->save();
                continue;
            } else {
                $logger->info('Found '.count($gamesWithSeries).' game(s) for series '.$seriesName);
            }

            $imageCounter = 0;

            foreach ($gamesWithSeries as $game) {

                $imageSquare = $game->image_square;
                $imageHeader = $game->image_header;

                $imageOffset = floor($imageCounter * ($imageWidth / count($gamesWithSeries)));
                //$logger->info("Counter: $imageCounter; Width: $imageWidth; Count: ".count($gamesToUse)."; Offset: ".$imageOffset);

                try {

                    $imageSquareFullPath = 'public/img/ps-square/'.$imageSquare;
                    if (file_exists($imageSquareFullPath)) {
                        $gameImage = Image::make($imageSquareFullPath);
                        $gameImage->resize(200, 200);
                        if (count($gamesWithSeries) == 3) {
                            $gameImage->crop(200, 200, 25, 0);
                        }

                        $img->insert($gameImage, 'left', $imageOffset, 0);
                    } else {
                        $logger->error($imageSquareFullPath.' - File not found');
                    }

                } catch (\Exception $e) {

                    $logger->error($imageSquareFullPath.' - '.$e->getMessage());

                }

                $imageCounter++;

            }

            $img->save(public_path('img/gen/series/'.$seriesFilename));

            $series->landing_image = $seriesFilename;
            $series->save();

        }
    }
}
