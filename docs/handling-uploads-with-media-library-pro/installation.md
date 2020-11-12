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

Route::mediaLibrary();
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
});
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

If you're using PurgeCSS, you might have to add a rule to your whitelist patterns.

```js
mix.purgeCss({ whitelistPatterns: [/^media-library/] });
```

### Directly in Blade/HTML

You should copy the built CSS from `vendor/spatie/laravel-medialibrary-pro/resources/js/media-library-pro-styles/dist/styles.css` into your `public` folder, and then use a `link` tag in your blade/html to get it: `<link rel="stylesheet" href="{{ asset('css/main.css') }}">`.

If you would like to customize the CSS we provide, head over to [the section on Customizing CSS](/docs/laravel-medialibrary/v9/handling-uploads-with-media-library-pro/customizing-css).

## Usage in a frontend repository

If you can't install the package using `composer` because, for example, you're developing an SPA, you can download the packages from GitHub Packages.

### Registering with GitHub Packages

You will need to create a Personal Access Token with the `read:packages` permission on the GitHub account that has access to the [spatie/laravel-media-library-pro](https://github.com/spatie/laravel-medialibrary-pro) repository. We suggest creating an entirely new token for this and not using it for anything else. You can safely share this token with team members as long as it has only this permission. Sadly, there is no way to scope the token to only the Media Library Pro repository.

Next up, create a `.npmrc` file in your project's root directory, with the following content:

_.npmrc_

```
//npm.pkg.github.com/:_authToken=github-personal-access-token-with-packages:read-permission
@spatie:registry=https://npm.pkg.github.com
```

Make sure the plaintext token does not get uploaded to GitHub along with your project. Either add the file to your `.gitignore` file, or set the token in your `.bashrc` file as an ENV variable.

_.bashrc_

```
export GITHUB_PACKAGE_REGISTRY_TOKEN=token-goes-here
```

_.npmrc_

```
//npm.pkg.github.com/:_authToken=${GITHUB_PACKAGE_REGISTRY_TOKEN}
@spatie:registry=https://npm.pkg.github.com
```

Alternatively, you can use `npm login` to log in to the GitHub Package Registry. Fill in your GitHub credentials, using your Personal Access Token as your password.

```
npm login --registry=https://npm.pkg.github.com --scope=@spatie
```

If you get stuck at any point, have a look at [GitHub's documentation on this](https://docs.github.com/en/free-pro-team@latest/packages/publishing-and-managing-packages/installing-a-package).

### Downloading the packages from GitHub Packages

Now, you can use `npm install --save` or `yarn add` to download the packages you need.

```
yarn add @spatie/media-library-pro-styles @spatie/media-library-pro-vue3-attachment
```

**You will now have to include the `@spatie/` scope when importing the packages**, this is different from examples in the documentation.

```
import { MediaLibraryAttachment } from '@spatie/media-library-pro-vue3-attachment';
```

You can find a list of all the packages on the repository: https://github.com/orgs/spatie/packages?repo_name=laravel-medialibrary-pro.
