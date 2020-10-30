---
title: Customizing CSS
weight: 9
---

If you want to change the look of the Media Library Pro components to match the style of your own app, you have multiple options.

## 1. Use your own Tailwind CSS configuration

Instead of importing/linking the pre-built `dist/styles.css` from the package, you can import the `src/styles.css` and run every `@apply` rule through your own `tailwind.config.js`.

```css
/* app.css */

@tailwind base;

@tailwind components;

@tailwind utilities;

@import "src/styles.css";

…
```

This is exactly what happens in the header of the homepage at [medialibrary.pro](https://medialibrary.pro): the shown component has a slightly blue-ish look, using the color palette of this site.


## 2. Override only portions in your CSS

If you only want to tinker with certain aspects of the component but like to keep the CSS in sync with future package updates, nothing stops you from overriding only certain CSS rules with your own tweaks. Every DOM-element of the component has a class with prefix `media-library`.

Let's say your thumbs aren't square and you want to show them in their original aspect ratio. 
Inspect the component in your browser to find out that the thumbnail is rendered in the DOM element with class `media-library-thumb-img`. Next, write some extra CSS for this class:

```css
/* app.css */

…

@import "src/styles.css";

.media-library-thumb-img {
    @apply object-contain;
}

```


## 3. Copy the CSS to your own project 

If you want to go full-option, you can always copy `src/styles.css` to your own project and go wild.
In this example we renamed the file to `custom/media-library.css`.
Beware: you will have to manually keep this CSS in sync with changes in future package updates. 

```css
/* app.css */

…

@import "custom/media-library.css";
```

One of the many changes we like to do, is detaching the error list at the top and give it rounded corners:

```css
/* custom/media-library.css */

…

.media-library-listerrors {
    …
    @apply mb-6;
    @apply rounded-lg;
    …
}
```

This is what we've done on the Customized Collection demo on [medialibrary.pro/demo-customized-collection](http://medialibrary.pro/demo-customized-collection). Pick a file that is too big to see the error list in effect. 
