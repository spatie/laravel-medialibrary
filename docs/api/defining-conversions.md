---
title: Defining conversions
weight: 2
---

A media conversion can be added to your model in the `registerMediaConversions`-function. It should start with a call to `addMediaConversion`. From there on you can use any of the methods available in the API. They are all chainable.

Take a look in the [Defining conversions section](/laravel-medialibrary/v7/converting-images/defining-conversions/)
for more details.

## General methods

### addMediaConversion

```php
/**
 * Add a conversion.
 *
 * @param string $name
 *
 * @return \Spatie\MediaLibrary\Conversion\Conversion
 */
public function addMediaConversion($name)
```

### performOnCollections

```php
/**
 * Set the collection names on which this conversion must be performed.
 *
 * @param string $collectionNames,...
 *
 * @return $this
 */
public function performOnCollections($collectionNames)
``` 

### queued

```php 
/**
 * Mark this conversion as one that should be queued.
 *
 * @return $this
 */
 public function queued()
```

### nonQueued

```php 
/**
 * Mark this conversion as one that should not be queued.
 *
 * @return $this
 */
public function nonQueued()
```

## Image manipulations

You may add any call to one of [the manipulation functions](https://docs.spatie.be/image) available on [the spatie/image package](https://github.com/spatie/image).

