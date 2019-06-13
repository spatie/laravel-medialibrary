---
title: Adding files
weight: 1
---

Adding a file to the medialibrary is easy. Just pick one of the starting methods, optionally add some of the middle methods
and finish with a finishing method. All start and middle methods are chainable.

For example:

```php
$yourModel
    ->addMedia($pathToFile) //starting method
    ->withCustomProperties(['mime-type' => 'image/jpeg']) //middle method
    ->preservingOriginal() //middle method
    ->toMediaLibrary(); //finishing method
```

## Starting methods

### addMedia

```php
/**
 * Add a file to the medialibrary. The file will be removed from
 * its original location.
 *
 * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
 *
 * @return \Spatie\MediaLibrary\FileAdder\FileAdder
 */
public function addMedia($file)
```

### <span class="badge">v3.8+</span> addMediaFromUrl

```php
/**
 * Add a remote file to the medialibrary.
 * 
 * @param $url
 *
 * @return mixed
 *
 * @throws \Spatie\MediaLibrary\Exceptions\UrlCouldNotBeOpened
 */
public function addMediaFromUrl($url)
```


### copyMedia


```php
/**
 * Copy a file to the medialibrary.
 *
 * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
 *
 * @return \Spatie\MediaLibrary\FileAdder\FileAdder
 */
public function copyMedia($file)
```

## Middle methods

### preserveOriginal

```php
/**
 * When adding the file the medialibrary, the original file
 * will be preserved.
 *
 * @return $this
 */
public function preservingOriginal()
```

### usingName

```php
/**
 * Set the name of the media object.
 *
 * @param $name
 *
 * @return $this
 */
public function usingName($name)
```

### setName

This is an alias for `usingName`

### usingFileName

```php
/**
 * Set the name of the file that is stored on disk.
 *
 * @param $fileName
 *
 * @return $this
 */
public function usingFileName($fileName)
```

### setFileName

This is an alias for `usingFileName`

### withCustomProperties

```php
/**
 * Set the metadata.
 *
 * @param array $customProperties
 *
 * @return $this
 */
public function withCustomProperties(array $customProperties)
```

## Finishing methods

### toMediaLibrary

```php
/**
 * Set the target media collection to default.
 * Will also start the import process.
 *
 * @param string $collectionName
 * @param string $diskName
 *
 * @return Media
 *
 * @throws FileDoesNotExist
 * @throws FileTooBig
 */
public function toMediaLibrary($collectionName = 'default', $diskName = ''
```

### toMediaLibraryOnDisk

This is an alias for `toMediaLibrary`

### toCollection

This is an alias for `toMediaLibrary`

### toCollectionOnDisk

This is an alias for `toMediaLibrary`
