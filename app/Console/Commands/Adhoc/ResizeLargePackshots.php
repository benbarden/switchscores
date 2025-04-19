<?php

namespace App\Console\Commands\Adhoc;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

use App\Services\Game\Images as GameImages;

class ResizeLargePackshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ResizeLargePackshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adhoc job to resize large packshots';

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

        $path = public_path(GameImages::PATH_IMAGE_SQUARE);

        $logger->info("Getting files in path: ".$path);

        $files = File::files($path);

        foreach ($files as $file) {

            $name = $file->getFilename();
            $size = $file->getSize();

            if ($size > 200000) {

                $logger->info('File above 200kb: '.$name.' - '.$size);

                // Copy original
                $currentPath = $path.$name;
                $destPath = $path.'originals/'.$name;
                if (file_exists($destPath)) {
                    $logger->info('File already updated; skipping');
                    continue;
                }

                $logger->info(sprintf('Copying from [%s] to [%s]', $currentPath, $destPath));
                File::copy($currentPath, $destPath);

                // Save at lower quality
                $logger->info("Saving at 90%");
                $img = Image::make($currentPath)->save($currentPath, 85);

                break;

            }

        }

        $logger->info('Complete');
    }
}
