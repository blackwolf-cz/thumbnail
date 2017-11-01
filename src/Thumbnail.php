<?php

namespace Noisim\Thumbnail;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;

class Thumbnail
{
    private $basePath;
    private $baseDir;
    private $thumbsPath;
    private $thumbsDir;

    function __construct()
    {
        Image::configure(array('driver' => config("thumb.driver", "gd")));
    }

    public function thumbnail($path, $width = null, $height = null, $type = 'crop', $bgColor = null)
    {
        $this->basePath = rtrim(config('thumb.base_path', '/'), '/');
        $this->baseDir = public_path($this->basePath);
        $path = ltrim($path, "/");

        if (is_null($width) && is_null($height)) {
            return $this->imageItself($path);
        }

        $this->createThumbsDir();

        /* If thumbnail already exist return it */
        if (File::exists($this->thumbsDir . "/" . "{$width}x{$height}/" . $path)) {
            return url($this->thumbsPath . "/" . "{$width}x{$height}/" . $path);
        }

        /* If original image doesn't exists return a default error image */
        if (!File::exists($this->baseDir . "/" . $path)) {
            return $this->noImage($width, $height, $type, $bgColor);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png', 'image/svg+xml', 'image/webp'];
        $contentType = mime_content_type($this->baseDir . "/" . $path);

        if (in_array($contentType, $allowedMimeTypes)) {

            /* If svg return the original image */
            if ($contentType == 'image/svg+xml') {
                return url($this->basePath . "/" . $path);
            }

            $image = Image::make($this->baseDir . "/" . $path);

            switch ($type) {
                case "resize": {
                    $image->resize($width, $height);
                    break;
                }
                case "crop": {
                    $image->fit($width, $height, function ($constraint) {
                        $constraint->upsize();
                    });
                    break;
                }
                case "keep-ratio": {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    break;
                }
                case "background": {
                    $bgColor = ($bgColor) ? $bgColor : "#ffffff";
                    $image->resizeCanvas($width, $height, 'center', false, $bgColor);
                }
            }

            $dir_path = (dirname($path) == '.') ? "" : "/" . dirname($path);

            /* Create the directory if it doesn't exist */
            if (!File::exists($this->thumbsDir . "/{$width}x{$height}" . $dir_path)) {
                File::makeDirectory($this->thumbsDir . "/{$width}x{$height}" . $dir_path, 0775, true);
            }

            /* Save the thumbnail */
            $image->save($this->thumbsDir . "/{$width}x{$height}/" . $path);

            /* Return the url of the thumbnail */
            return url($this->thumbsPath . "/{$width}x{$height}/" . $path);
        } else {
            return $this->noImage($width, $height, $type, $bgColor);
        }
    }

    public function imageItself($path)
    {
        return url("{$this->basePath}/{$path}");
    }

    public function noImage($width, $height, $type, $bgColor)
    {
        if (config("thumb.error_image")) {

            /* 1. Recursive call to generate default error image */

            return $this->thumbnail(config("thumb.error_image"), $width, $height, $type);
        } else {

            /* 2. Returns a placeholder image generated from a host based on your config (default: placeholder.com) */

            if (config("thumb.placeholder")) {
                $placeholder = nth_format(config("thumb.placeholder"), [
                    "width" => $width,
                    "height" => $height,
                    "bgColor" => $bgColor,
                ]);
                return $placeholder;
            }
            return "http://via.placeholder.com/{$width}x{$height}/fff/111";
        }
    }

    private function createThumbsDir()
    {
        $this->thumbsPath = rtrim($this->basePath, "/") . "/" . trim(config('thumb.thumbs_dir_name', 'thumbs'), '/');
        $this->thumbsDir = rtrim($this->baseDir, "/") . "/" . trim(config('thumb.thumbs_dir_name', 'thumbs'), '/');
        if (!file_exists($this->thumbsDir)) {
            return mkdir($this->thumbsDir);
        }
    }
}
