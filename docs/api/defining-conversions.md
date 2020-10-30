---
title: Defining conversions
weight: 2
---

A media conversion can be added to your model in the `registerMediaConversions`-function. It should start with a call to `addMediaConversion`. From there on you can use any of the methods available in the API. They are all chainable.

Take a look in the [Defining conversions section](/laravel-medialibrary/v9/converting-images/defining-conversions/)
for more details.

## General methods

### addMediaConversion

```php
/*
 * Add a conversion.
 */
public function addMediaConversion(string $name): \Spatie\MediaLibrary\Conversions\Conversion
```

### performOnCollections

```php
/*
 * Set the collection names on which this conversion must be performed.
 *
 * @param string $collectionNames,...
 */
public function performOnCollections($collectionNames): self
``` 

### queued

```php 
/*
 * Mark this conversion as one that should be queued.
 */
 public function queued(): self
```

### nonQueued

```php 
/*
 * Mark this conversion as one that should not be queued.
 */
public function nonQueued(): self
```

### useLoadingAttributeValue

This is the value that, when this conversation is converted to html, will be used in the `loading` attribute. The loading attribute is a standardised attribute that controls lazy loading behaviour of the browser. Possible values are `lazy`, `eager`, `auto` or null if you don't want to set any loading instruction.

You can learn more on native lazy loading [in this post on css-tricks](https://css-tricks.com/native-lazy-loading/).

## Image manipulations

You may add any call to one of [the manipulation functions](https://docs.spatie.be/image) available on [the spatie/image package](https://github.com/spatie/image).

