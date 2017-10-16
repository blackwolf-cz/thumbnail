<?php

namespace Noisim\Thumbnail;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Noisim\Thumbnail\Commands\ClearThumbnails;

class ThumbnailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/thumb.php' => config_path('thumb.php'),
        ], 'config');

        require "helpers.php";

        Blade::directive('thumbnail', function ($expression) {
            $vars = explode(',', str_replace(['(', ')', ' '], '', $expression));
            $path = isset($vars[0]) ? $vars[0] : "null";
            $width = isset($vars[1]) ? $vars[1] : "null";
            $height = isset($vars[2]) ? $vars[2] : "null";
            $type = isset($vars[3]) ? $vars[3] : "null";
            $bgColor = isset($vars[4]) ? $vars[4] : "null";
            return "<?php echo '<img class=\"nth-thumbnail\" src=\"' . thumbnail('$path', $width, $height, '$type', '$bgColor') . '\" alt=\"$path\"/>' ?>";
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearThumbnails::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind("thumbnail", \Noisim\Thumbnail\Thumbnail::class);
    }
}
