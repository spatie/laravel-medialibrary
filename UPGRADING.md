# Upgrading

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

## From v7 to v8

- the namespace has been renamed from `Spatie\Medialibrary` to `Spatie\Medialibrary`. Make sure you modified all classes that reference media library classes.
- replace all code occurrences of `mediaLibrary` by `medialibrary`
- Add a `conversions_disk` field to the `media` table (you'll find the definition in the migrations file of the package) and for each row copy the value of `disk` to `conversions_disk`.
- Add a `uuid` field to the `media` table and fill each row with a unique value, preferably a `uuid`
- Url generation has been vastly simplified. You should set the `url_generator` in the `medialibrary` config file to `Spatie\Medialibrary\UrlGenerator\DefaultUrlGenerator::class`. It will be able to handle most disks.
- remove the `s3.domain` key from the `medialibrary` config file
- spatie/pdf-to-image is now a suggestion dependency. Make sure to install it, if you want to create thumbnails for PDFs or SVGs
- `registerMediaConversions` and `registerMediaCollections` should now use the  `void` return type.
- if the `path_generator` key in the `medialibrary` config file was set to `null`, change the value to `Spatie\Medialibrary\PathGenerator\DefaultPathGenerator::class`
- the `rawUrlEncodeFilename` method on `BaseUrlGenerator` has been removed. Remove all calls in your own code to this method.
- `getConversionFile` on `Conversion` now accepts a `Media` instance instead of a `string`. In normal circumstance you wouldn't have used this function directly.

## 7.3.0

- Before `hasGeneratedConversion` will work, the custom properties 
of every media item will have to be re-written in the database, or all conversions must be regenerated.
This won't break any existing code, but in order to use the new feature, you will need to do a manual update of your media items.

## 7.1.0

- The `Filesystem` interface is removed, and the `DefaultFilesystem` implementation is renamed to `Filesystem`.
If you want your own filesystem implementation, you should extend the `Filesystem` class.
- The method `Filesytem::renameFile(Media $media, string $oldFileName)` was renamed to `Filesystem::syncFileNames(Media $media)`. If you're using your own implementation of `Filesystem`, please update the method signature.
- The `default_filesystem` config key has been changed to `disk_name`.
- The `custom_url_generator_class` and `custom_path_generator_class` config keys have been changed to `url_generator` and `path_generator`. (commit ba46d8008d26542c9a5ef0e39f779de801cd4f8f)

## From v6 to v7

- add the `responsive_images` column in the media table: `$table->json('responsive_images');`
- rename the `use Spatie\Medialibrary\HasMedia\Interfaces\HasMedia;` interface to `use Spatie\Medialibrary\HasMedia\HasMedia;`
- rename the `use Spatie\Medialibrary\HasMedia\Interfaces\HasMediaConversions;` interface to `use Spatie\Medialibrary\HasMedia\HasMedia;` as well (the distinction was [removed](https://github.com/spatie/laravel-medialibrary/commit/48f371a7b10cc82bbee5b781ab8784acc5ad0fc3#diff-f12df6f7f30b5ee54d9ccc6e56e8f93e)).
- all converted files should now start with the name of the original file. TODO: add instructions / or maybe a script
- `Spatie\Medialibrary\Media` has been moved to `Spatie\Medialibrary\Models\Media`. Update the namespace import of `Media` accross your app
- The method definitions of `Spatie\Medialibrary\Filesystem\Filesystem::add` and `Spatie\Medialibrary\Filesystem\Filesystem::copyToMedialibrary` are changed, they now use nullable string typehints for `$targetFileName` and `$type`.

## From v5 to v6

- the signature of `registerMediaConversions` has been changed.

Change every instance of

  ```php
  public function registerMediaConversions()
  ```
to

 ```php
 public function registerMediaConversions(Media $media = null)
 ```

 - change the `defaultFilesystem` key in the config file to `default_filesystem`
 - add the `image_optimizers` key from the default config file to your config file.
 - be aware that the medialibrary will now optimize all conversions by default. If you do not want this tack on `nonOptimized` to all your media conversions.
 - `toMedialibrary` has been removed. Use `toMediaCollection` instead.
 - `toMedialibraryOnCloudDisk` has been removed. Use `toMediaCollectionOnCloudDisk` instead.


## From v4 to v5
- rename `config/laravel-medialibrary` to `config/medialibrary.php`. Some keys have been added or renamed. Please compare your config file againt the one provided by this package
- all calls to `toCollection` and `toCollectionOnDisk` and `toMedialibraryOnDisk` should be renamed to `toMedialibrary`
- media conversions are now handled by `spatie/image`. Convert all manipulations on your conversion to manipulations supported by `spatie/image`.
- add a `mime_type` column to the `media` table, manually populate the column with the right values.
- calls to `getNestedCustomProperty`, `setNestedCustomProperty`, `forgetNestedCustomProperty` and `hasNestedCustomProperty` should be replaced by their non-nested counterparts.
- All exceptions have been renamed. If you were catching medialibrary specific exception please look up the new name in /src/Exceptions.
- be aware`getMedia` and related functions now return only the media from the `default` collection
- `image_generators` have now been added to the config file.


## From v3 to v4
- All exceptions have been renamed. If you were catching medialibrary specific exception please look up the new name in /src/Exceptions.
- Glide has been upgraded from 0.3 in 1.0. Glide renamed some operations in their 1.0 release, most notably the `crop` and `fit` ones. If you were using those in your conversions refer the Glide documentation how they should be changed.

## From v2 to v3
You can upgrade from v2 to v3 by performing these renames in your model that has media.

- `Spatie\Medialibrary\HasMediaTrait` has been renamed to `Spatie\Medialibrary\HasMedia\HasMediaTrait`.
- `Spatie\Medialibrary\HasMedia` has been renamed to `Spatie\Medialibrary\HasMedia\Interfaces\HasMediaConversions`
- `Spatie\Medialibrary\HasMediaWithoutConversions` has been renamed to `Spatie\Medialibrary\HasMedia\Interfaces\HasMedia`

In the config file you should rename the `filesystem`-option to `default_filesystem`.

In the db the `temp`-column must be removed. Add these columns:
- disk (varchar, 255)
- custom_properties (text)
You should set the value of disk column in all rows to the name the default_filesystem specified in the config file.

Note that this behaviour has changed:
- when calling `getMedia()` without providing a collection name all media will be returned (whereas previously only media
from the default collection would be returned)
- calling `hasMedia()` without a collection name returns true if any given collection contains files (wheres previously
it would only return try if files were present in the default collection)
- the `addMedia`-function has been replaced by a fluent interface.

## From v1 to v2
Because v2 is a complete rewrite a simple upgrade path is not available.
If you want to upgrade completely remove the v1 package and follow install instructions of v2.
