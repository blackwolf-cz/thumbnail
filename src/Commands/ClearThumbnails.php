<?php

namespace Noisim\Thumbnail\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnail:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all the thumbnails directory so they can be regenerated.';

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
        $this->basePath = rtrim(config('thumb.base_path', '/'), '/');
        $this->baseDir = public_path($this->basePath);
        $this->thumbsDir = rtrim($this->baseDir, "/") . "/" . trim(config('thumb.thumbs_dir_name', 'thumbs'), '/');

        $success = File::cleanDirectory($this->thumbsDir, true);
        if ($success) {
            $this->info('Thumbnails has been cleared successfully.');
        } else {
            $this->error('Something went wrong!');
        }
    }
}
