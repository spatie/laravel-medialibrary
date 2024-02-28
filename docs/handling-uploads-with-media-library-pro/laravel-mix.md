---
title: Laravel Mix
weight: 9
---

## Using Laravel Mix or Webpack with css-loader

You can import the built CSS in your own CSS files using `@import "vendor/spatie/laravel-medialibrary-pro/resources/js/media-library-pro-styles";`.

This isn't a very pretty import, but you can make it cleaner by adding this configuration to your Webpack config:

**laravel-mix >6**

```js
mix.override((webpackConfig) => {
    webpackConfig.resolve.modules = [
        "node_modules",
        __dirname + "/vendor/spatie/laravel-medialibrary-pro/resources/js",
    ];
});
```

**laravel-mix <6**

```js
mix.webpackConfig({
    resolve: {
        modules: [
            "node_modules",
            __dirname + "/vendor/spatie/laravel-medialibrary-pro/resources/js",
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

## Vue specific configuration

**laravel-mix >6**

```js
// webpack.mix.js

mix.override((webpackConfig) => {
    webpackConfig.resolve.modules = [
        "node_modules",
        __dirname + "/vendor/spatie/laravel-medialibrary-pro/resources/js",
    ];
});
```

**laravel-mix <6**

```js
// webpack.mix.js

mix.webpackConfig({
    resolve: {
        modules: [
            "node_modules",
            __dirname + "/vendor/spatie/laravel-medialibrary-pro/resources/js",
        ],
    },
});
```

This will force Webpack to look in `vendor/spatie/laravel-medialibrary-pro/resources/js` when resolving imports, and allows you to shorten your import.

```js
import { MediaLibraryAttachment } from "media-library-pro-vue3-attachment";
```

If you're using TypeScript, you will also have to add this to your tsconfig:

```json
// tsconfig.json

{
    "compilerOptions": {
        "paths": {
            "*": ["*", "vendor/spatie/laravel-medialibrary-pro/resources/js/*"]
        }
    }
}
```
