---
title: Disable CDN
weight: 12
---

Media Library Pro uses a CDN to load the [Sortable](https://github.com/SortableJS/Sortable) library. Sortable provides the drag-and-drop-ability inside a Media Library Pro component.

If you would like to disable the loading via CDN and want to take care by yourself of including Sortable you will need to add the following option to your `media-library.php` config file.

```php
    ...
    /*
     * When disabling this option, Media Library Pro don't include a script tag to load the Sortable library via a CDN.
     * You have to include the Sortable library by yourself!
     */
    'include_sortable_cdn_script' => false,
    ...
```
You can install Sortable within your project dependencies and include it in your `app.js`.

```js
import Sortable from 'sortablejs'

window.Sortable = Sortable
```
