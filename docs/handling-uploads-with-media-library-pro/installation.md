---
title: Installation
weight: 2
---

[Media Library Pro](medialibrary.pro) is a paid add-on package for Laravel Media Library. In order to use it, you must have the base version of media library installed in your project. Here are [the installation instructions for the base version](/docs/laravel-medialibrary/v11/installation-setup).

## Installing the base package

If you haven't installed the base Media Library package, make sure to do so by running:

```bash
composer require "spatie/laravel-medialibrary:^11.0.0"
```

## Getting a license

You must buy a license on [the Media Library Pro product page](https://spatie.be/products/media-library-pro) at spatie.be

Single application licenses maybe installed in a single Laravel app. In case you bought the unlimited application license, there are no restrictions. A license comes with one year of upgrades. If a license expires, you are still allowed to use Media Library Pro, but you won't get any updates anymore.

## Current version

The current version of Media Library Pro is v4.

You will find upgrade instructions [here](/docs/laravel-medialibrary/v11/handling-uploads-with-media-library-pro/upgrading).

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


To be sure you can reach `satis.spatie.be`,  clean your autoloaders before using this command:

```bash
composer dump-autoload
```

To validate if Composer can read your auth.json you can run this command:

```bash
composer config --list --global | grep satis.spatie.be
```

If you are using [Laravel Forge](https://forge.laravel.com), you don't need to create the `auth.json` file manually. Instead, you can set the credentials on the Composer Package Authentication screen of your server. Fill out the fields with these values:

- Repository URL: `satis.spatie.be`
- Username: Fill this field with your spatie.be account email address
- Password: Fill this field with your Media Library Pro license key

![screenshot](/docs/laravel-medialibrary/v11/images/forge.png)

With the configuration above in place, you'll be able to install the Media Library Pro into your project using this command:

```bash
composer require "spatie/laravel-medialibrary-pro:^5.1.0"
```

## Prepare the database

Media Library Pro tracks all temporary uploads in a table called `temporary_uploads`.

To create the table you need to publish and run the migration:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibraryPro\MediaLibraryProServiceProvider" --tag="media-library-pro-migrations"
php artisan migrate
```

## Automatically removing temporary uploads

If you are using Media Library Pro, you must schedule this artisan command in `app/Console/Kernel` to automatically delete temporary uploads

### Laravel >= 11
```php
// in bootstrap/app.php

->withSchedule(function (Schedule $schedule) {
    $schedule->command('media-library:delete-old-temporary-uploads')->daily();
})
```

### Laravel < 11
```php
// in app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    $schedule->command('media-library:delete-old-temporary-uploads')->daily();
}
```

## Add the route macro

To accept temporary uploads via React and Vue, you must add this macro to your routes file. 
You do not need to register this endpoint when using the Blade/Livewire components.

```php
// Probably routes/web.php

Route::mediaLibrary();
```

This macro will add the routes to controllers that accept file uploads for all components.

## Front-end setup

You have a couple of options for how you can use the UI components' CSS, depending on your and your project's needs:

### Using Vite
In your vite.config.js file you can add an alias to the Medialibrary Pro css file:

```javascript
export default defineConfig({
    resolve: {
        alias: {
            'media-library-pro-styles': __dirname + '/vendor/spatie/laravel-medialibrary-pro/resources/js/media-library-pro-styles/src/styles.css',
        }
    }
});
```

This will allow you to import the file in your own css file like this:
```css
@import "media-library-pro-styles";
```


### Directly in Blade/HTML

You should copy the built CSS from `vendor/spatie/laravel-medialibrary-pro/resources/js/media-library-pro-styles/dist/styles.css` into your `public` folder, and then use a `link` tag in your blade/html to get it: `<link rel="stylesheet" href="{{ asset('css/main.css') }}">`.

If you would like to customize the CSS we provide, head over to [the section on Customizing CSS](/docs/laravel-medialibrary/v11/handling-uploads-with-media-library-pro/customizing-css).


## What happens when your license expires?

A few days before a license expires, we'll send you a reminder mail to renew your license.

Should you decide not to renew your license, you won't be able to use composer anymore to install this package. You won't get any new features or bug fixes.

Instead, you can download a zip containing the latest version that your license covered. This can be done on  [your purchases page on spatie.be](https://spatie.be/profile/purchases). You are allowed to host this version in a private repo of your own.
