---
title: Disable CDN
weight: 12
---

Media Library Pro loads [Sortable](https://github.com/SortableJS/Sortable) from a CDN to power drag-and-drop sorting in its components.

If you'd rather bundle Sortable yourself, add `include_sortable_cdn_script` set to `false` in `config/media-library.php`:

```php
// config/media-library.php

'include_sortable_cdn_script' => false,
```

Then install `sortablejs` via npm and expose it on `window` in your `app.js`:

```js
import Sortable from 'sortablejs';

window.Sortable = Sortable;
```
