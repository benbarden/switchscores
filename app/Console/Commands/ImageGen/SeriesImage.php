<?php

namespace App\Console\Commands\ImageGen;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Domain\Game\ImageResolver;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;

use Intervention\Image\Facades\Image;

class SeriesImage extends Command
{
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
    private $imageResolver;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        GameListsRepository $repoGameLists,
        GameSeriesRepository $repoGameSeries,
        ImageResolver $imageResolver
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->repoGameSeries = $repoGameSeries;
        $this->imageResolver = $imageResolver;
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
            $imagesAdded = 0;

            foreach ($gamesWithSeries as $game) {

                $imageSquareKey = $this->imageResolver->storageKey(
                    $game, ImageResolver::TYPE_SQUARE, $game->images->square_filename
                );

                $imageOffset = floor($imageCounter * ($imageWidth / count($gamesWithSeries)));
                //$logger->info("Counter: $imageCounter; Width: $imageWidth; Count: ".count($gamesToUse)."; Offset: ".$imageOffset);

                try {

                    $imageSquareData = Storage::disk(ImageResolver::DISK)->get($imageSquareKey);
                    if ($imageSquareData) {
                        $gameImage = Image::make($imageSquareData);
                        $gameImage->resize(200, 200);
                        if (count($gamesWithSeries) == 3) {
                            $gameImage->crop(200, 200, 25, 0);
                        }

                        $img->insert($gameImage, 'left', $imageOffset, 0);
                        $imagesAdded++;
                    } else {
                        $logger->error($imageSquareKey.' - File not found');
                    }

                } catch (\Exception $e) {

                    $logger->error($imageSquareKey.' - '.$e->getMessage());

                }

                $imageCounter++;

            }

            if ($imagesAdded == 0) {
                // Don't overwrite a good image with a blank one if Spaces couldn't be read
                $logger->error('No packshots loaded for series '.$seriesName.'; keeping existing image');
                continue;
            }

            $img->save(public_path('img/gen/series/'.$seriesFilename));

            $series->landing_image = $seriesFilename;
            $series->save();

        }
    }
}
