Thumbnail is a Laravel package that you can use to optimize the dimensions of your images on your site or web app. So every time you need an image with specific dimensions you only provide them and the image will be automatically generated for you.

## How to use

This package is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "noisim/thumbnail": "dev-master"
    }
}
```

and run composer to update the dependencies `composer update`.

Then open your Laravel config file config/app.php and in the `$providers` array add the service provider for this package.

```php
\Noisim\Thumbnail\ThumbnailServiceProvider::class
```

Finally generate the configuration file running in the console:
```
php artisan vendor:publish --tag=config
```

