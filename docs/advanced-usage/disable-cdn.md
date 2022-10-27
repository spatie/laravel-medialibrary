---
title: Disable CDN
weight: 12
---

Media Library Pro uses a CDN to load the [Dragula](https://github.com/bevacqua/dragula) library. Dragula provides the drag-and-drop-ability inside a Media Library Pro component.

If you would like to disable the loading via CDN and want to take care by yourself of including Dragula you will need to add the following option to your `media-library.php` config file.

```php
    ...
    /*
    * When disabling this option, Media Library Pro don't include a script tag to load the dragula library via a CDN.
    * You have to include the dragula library by yourself!
    */
    'include_dragula_cdn_script' => false,
    ...
```
You can install Dragula within your project dependencies and include it in your `app.js`.

```js
import dragula from 'dragula';

window.dragula = dragula;
```
