<?php

namespace Noisim\Thumbnail;

use Intervention\Image\ImageManagerStatic as Image;

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

    public function thumbnail($path, $width, $height, $type)
    {
        $this->basePath = rtrim(config('thumb.base_dir', '/'), '/');
        $this->baseDir = public_path($this->basePath);
        $path = ltrim($path, "/");

        if (is_null($width) && is_null($height)) {
            // Return the original Image
            return $this->imageItself($path);
        }

        $this->createThumbsDir();

        //if thumbnail exist returns it
        if (file_exists($this->thumbsDir . "/" . "{$width}x{$height}/" . $path)) {
            return url($this->thumbsPath . "/" . "{$width}x{$height}/" . $path);
        }

        //If original image doesn't exists returns a default image which shows that original image doesn't exist.
        if (!file_exists(public_path($this->basePath . "/" . $path))) {
            return $this->noImage($width, $height, $type);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png'];
        $contentType = mime_content_type($this->baseDir . "/" . $path);

        if (in_array($contentType, $allowedMimeTypes)) {

            $image = Image::make($this->baseDir . "/" . $path);

            switch ($type) {
                case "resize": {
                    $image->resize($width, $height);
                }
                case "fit": {
                    $image->fit($width, $height, function ($constraint) {
                        $constraint->upsize();
                    });
                    break;
                }
                case "background": {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                case "resizeCanvas": {
                    $image->resizeCanvas($width, $height, 'center', false, 'rgba(0, 0, 0, 0)');
                }
            }

            //relative directory path starting from main directory of images
            $dir_path = (dirname($path) == '.') ? "" : dirname($path);

            //Create the directory if it doesn't exist
            if (!file_exists($this->thumbsDir . "/{$width}x{$height}/" . $dir_path)) {
                mkdir($this->thumbsDir . "/{$width}x{$height}/" . $dir_path, 0775, true);
            }

            //Save the thumbnail
            $image->save($this->thumbsDir . "/{$width}x{$height}/" . $path);

            //return the url of the thumbnail
            return url($this->thumbsPath . "/{$width}x{$height}/" . $path);
        } else {

            $this->noImage($width, $height, $type);
        }
    }

    public function imageItself($path)
    {
        return url("{$this->baseDir}/{$path}");
    }

    public function noImage($width, $height, $type)
    {
        if (config("thumb.error_image")) {
            // 1. Recursive call to generate default error image
            $this->thumbnail(config("thumb.error_image"), $width, $height, $type);
        } else {
            // 2. Returns an image placeholder generated from placeholder.com

            if (config("thumb.placeholder")) {
                $placeholder = nth_format(config("thumb.placeholder"), [
                    "width" => $width,
                    "height" => $height
                ]);
                return $placeholder;
            }
            return "http://via.placeholder.com/{$width}x{$height}/fff/111";
        }
    }

    private function createThumbsDir()
    {
        $this->thumbsPath = trim(config('thumb.thumbs_dir_name', 'thumbs'), '/');
        $this->thumbsDir = $this->baseDir . $this->thumbsPath;
        return mkdir($this->thumbsDir);
    }
}