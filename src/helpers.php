<?php

/* Helpers Functions */

if (!function_exists("thumbnail")) {
    function thumbnail($path, $width = null, $height = null, $type = "fit", $bgColor = null)
    {
        return (new \Noisim\Thumbnail\Thumbnail())->thumbnail($path, $width, $height, $type, $bgColor);
    }
}

if (!function_exists("nth_format")) {
    function nth_format($msg, $vars)
    {
        $vars = (array)$vars;
        return str_replace(
            array_map(function ($k) {
                return '{' . $k . '}';
            }, array_keys($vars)),

            array_values($vars),

            $msg
        );
    }
}