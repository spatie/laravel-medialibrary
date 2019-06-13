---
title: Using your own model
weight: 4
---

A custom model can be used in version 3.4.0 and higher.
This allows you to add your own fields, add relationships and so on.

The easiest way to use your own custom model would be to extend the 
default `Spatie\MediaLibrary\Media`-class. Here's an example:

```php
namespace YourApp\Models;
use Spatie\MediaLibrary\Media as BaseMedia;

class Media extends BaseMedia 
{
...
```

In the config file of the package you must specify the name of your custom class:

```php
// config/laravel-medialibrary.php
...
   'media_model' => YourApp\Models\Media::class
...
```
