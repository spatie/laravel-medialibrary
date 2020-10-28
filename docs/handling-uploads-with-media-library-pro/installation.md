---
title: Installation
weight: 2
---

[Media Library Pro](medialibrary.pro) is a paid add-on package for Laravel Media Library. In order to use it, you must have the base version of media library installed in your project. Here are [the installation instructions for the base version](/docs/laravel-medialibrary/v9/installation-setup).

## Installing the base package

## Getting a license

You must buy a license on [the Media Library Pro product page](https://spatie.be/products/media-library-pro) at spatie.be

Single application licenses maybe installed in a single Laravel app. In case you bought the unlimited application license, there are no restrictions. A license comes with one year of upgrades. If a license expires, you are still allowed to use Media Library Pro, but you won't get any updates anymore.

## Requiring Media Library Pro

After you've purchased a license, add the `satis.spatie.be` repository in your `composer.json`.

```php
"repositories": [
    {
        "type": "composer",
        "url": "https://satis.spatie.be"
    }
],
```

Next, you need to create a file called `auth.json` and place it either next to the `composer.json` file in your project, or in the Composer home directory. You can determine the Composer home directory on \*nix machines by using this command.

```bash
composer config --list --global | grep home
```

This is the content you should put in `auth.json`:

```php
{
    "http-basic": {
        "satis.spatie.be": {
            "username": "<YOUR-SPATIE.BE-ACCOUNT-EMAIL-ADDRESS-HERE>",
            "password": "<YOUR-MEDIA-LIBRARY-PRO-LICENSE-KEY-HERE>"
        }
    }
}
```

With the configuration above in place, you'll be able to install the Media Library Pro into your project using this command:

```bash
composer require "spatie/laravel-medialibrary-pro:^1.0.0"
```

## Prepare the database

Media Library Pro tracks all temporary uploads in a table called `temporary_uploads`.

To create the table you need to publish and run the migration:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibraryPro\MediaLibraryProServiceProvider" --tag="migrations"
php artisan migrate
```

## Automatically removing temporary uploads

If you are using Media Library Pro, you must schedule this artisan command in `app/Console/Kernel` to automatically delete temporary uploads

```php
// in app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    $schedule->command('media-library:delete-old-temporary-uploads')->daily();
}
```

## Add the route macro

To accept temporary uploads, you must add this macro to your routes file.

```php
// Probably routes/web.php

Route::temporaryUploads('temporary-uploads');
```

## Using the CSS

You have a couple of options for how you can use the UI components' CSS, depending on your and your project's needs:

### Using Laravel Mix or Webpack with css-loader

You can import the built CSS in your own CSS files using `@import(vendor/spatie/laravel-medialibrary-pro/resources/js/media-library-pro-styles)`. 

This isn't a very pretty import, but you can make it cleaner by adding this configuration to your Webpack config:

**laravel-mix >6**

```js
mix.override((webpackConfig) => {
    webpackConfig.resolve.modules = [
        "node_modules",
        __dirname + "/vendor/spatie/laravel-media-library-pro/resources/js",
    ];
}
```

**laravel-mix <6**

```js
mix.webpackConfig({
    resolve: {
        modules: [
            "node_modules",
            __dirname + "/vendor/spatie/laravel-media-library-pro/resources/js",
        ],
    },
});
```

This will force Webpack to look in `vendor/spatie/laravel-medialibrary-pro/resources/js` when resolving imports, and allows you to shorten your import to this:

```css
@import "media-library-pro-styles";
```

### Directly in Blade/HTML

You should copy the built CSS from `vendor/spatie/laravel-medialibrary-pro/resources/js/media-library-pro-styles/dist/styles.css` into your `public` folder, and then use a `link` tag in your blade/html to get it: `<link rel="stylesheet" href="{{ asset('css/main.css') }}">`.

If you would like to customize the CSS we provide, head over to [the section on Customizing CSS](/docs/laravel-medialibrary/v9/handling-uploads-with-media-library-pro/customizing-css).
