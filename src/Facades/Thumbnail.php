<?php

namespace Noisim\Thumbnail\Facades;

use Illuminate\Support\Facades\Facade;

class Thumbnail extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'thumbnail';
    }
}