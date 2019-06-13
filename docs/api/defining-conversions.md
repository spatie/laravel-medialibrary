---
title: Defining conversions
weight: 2
---

A media conversion can be added to your model in the `registerModelConversions`-function.
It should start with a call to `addMediaConversion`. From there on you can use any of
the methods available in the API. They are all chainable.

Take a look in the [Defining conversions section](/laravel-medialibrary/v3/converting-images/defining-conversions/)
for more details.

## General methods

### addMediaConversion

```php
/**
 * Add a conversion.
 *
 * @param string $nam
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

### setManipulations

Note: you should pass an array with Glide parameters to `$manipulations`.

```php
/**
 * Set the manipulations for this conversion.
 *
 * @param string $manipulations,...
 *
 * @return $this
 */
public function setManipulations($manipulations)
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

## Glide convenience methods

### setWidth
```php 
/**
 * Set the target width.
 * Matches with Glide's 'w'-parameter.
 *
 * @param int $width
 *
 * @return $this
 *
 * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversionParameter
 */ 
public function setWidth($width)
```

### setHeight

```php 
/*
 * Set the target height.
 * Matches with Glide's 'h'-parameter.
 *
 * @param int $height
 *
 * @return $this
 *
 * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversionParameter
 */
 ```
 
### setFormat
 
```php
/**
 * Set the target format.
 * Matches with Glide's 'fm'-parameter.
 *
 * @param string $format
 *
 * @return $this
 *
 * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversionParameter
 */
 public function setFormat($format)
 ``` 
 
### setFit
 
```php 
/**
 * Set the target fit.
 * Matches with Glide's 'fit'-parameter.
 *
 * @param string $fit
 *
 * @return $this
 *
 * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversionParameter
 */
 public function setFit($fit)
 ```
 
### setRectangle
 
```php 
/**
 * Set the target rectangle.
 * Matches with Glide's 'rect'-parameter.
 *
 * @param int $width
 * @param int $height
 * @param int $x
 * @param int $y
 *
 * @return $this
 *
 * @throws InvalidConversionParameter
 */
public function setRectangle($width, $height, $x, $y)
```
