# Changelog

All notable changes to `laravel-medialibrary` will be documented in this file

## 6.6.3 - 2017-11-28

- fix clearing entire media collection except a single media instance 

## 6.6.2 - 2017-11-07

- improve config comments

## 6.6.1 - 2017-11-02

- fixed the spelling of the `getFirstTemporaryUrl` method

## 6.6.0 - 2017-11-02

- add `getFirstTemporaryUrl`

## 6.5.0 - 2017-10-24

- add `only-missing` and `only` options to the `medialibrary:regenerate` command

## 6.4.2 - 2017-10-20

- fix correct minimal versions for `league/flysystem` and `spatie/image` when using `--prefer-lowest` option with composer

## 6.4.1 - 2017-10-19

- fix deletion of files when using a custom path generator

## 6.4.0 - 2017-10-16

- implement `Responsable` interface
- improve sanitizing filenames

## 6.3.0 - 2017-10-16

- add `sanitizingFileName`

## 6.2.1 - 2017-10-16

- fix for working with large files

## 6.2.0 - 2017-10-10

- add `ConversionWillStart` event

## 6.1.3 - 2017-10-02

- fixed URL-encoding for S3 files

## 6.1.2 - 2017-09-25

- bugfix: `getTemporaryUrl` now uses disk name instead of disk driver

## 6.1.1 - 2017-09-19

- bugfix: remove `getTemporaryUrl` method from `UrlGenerator` interface

## 6.1.0 - 2017-09-19

- add `getTemporaryUrl` method for media stored on S3

## 6.0.0 - 2017-08-30

- add compatiblity for Laravel 5.5
- dropped support for older Laravel versions
- the signature of `registerMediaConversions` has been changed
- the default disk has changed from `media` to `public`
- `defaultFilesystem` config option has been renamed to `default_filesystem`

## 5.14.0 - 2017-08-25

- add `getPath` to `S3UrlGenerator`

## 5.13.2 - 2017-08-03

- fix error when try to close a file that was already closed by flysystem

## 5.13.1 - 2017-08-03
- fix `MediaCannotBeDeleted` exception

## 5.13.0 - 2017-07-22

- add support for soft deletes

## 5.12.1 - 2017-07-11

- fixed array fields in `addMultipleMediaFromRequest`

## 5.12.0 - 2017-05-30

- add parameter to specify allowed mime types to `addMediaFromUrl` and `addMediaFromBase64`

## 5.11.1 - 2017-05-23

- fix bugs regarding attaching media to non-existing models

## 5.11.0 - 2017-05-10

- add support to `addAllMediaFromRequest` for file names in the request that contain an array

## 5.10.0 - 2017-04-18

- show progress bar when regenerating media

## 5.9.0 - 2017-04-12

- media can now be attached to unsaved models

## 5.8.2 - 2017-04-03

- fix bug where `mediaIsPreloaded` always returned true

## 5.8.1 - 2017-03-30

- fix bug where the wrong extension would be returned by `getResultExtension` for `keepOriginalImageFormat`

## 5.8.0 - 2017-03-24

- add `clearMediaCollectionExcept` method

## 5.7.0 - 2017-03-23

- add `keepOriginalImageFormat` manipulation

## 5.6.0 - 2017-03-22

- add `toMediaCollection`

## 5.5.3 - 2017-03-16

- fix bug where streams would be used on external filesystems that do not support streaming

## 5.5.2 - 2017-03-08

- prevent migration from being published multiple times
- `LocalUrlGenerator` will now use the `url` property of `disk` when one has been set

## 5.5.1 - 2017-03-08

- fix for using `MediaRepository` with a custom media model

## 5.5.0 - 2017-03-08

- add `createMultipleFromRequest` and `createAllFromRequest` on `FileAdder`

## 5.4.0 - 2017-03-08

- add `temporary_directory_path` to config

## 5.3.3 - 2017-03-06

- fix bug around `getRemoteHeadersForFile()`

## 5.3.2 - 2017-03-01

- fix for undefined function `getPath` when using S3

## 5.3.1 - 2017-03-01

**this version is broken, do not use**

- fix for undefined function `getPath` when using S3

## 5.3.0 - 2017-02-22

- add support for `registerMediaConversionsUsingModelInstance`

## 5.2.0 - 2017-02-20

- add `addMediaFromBase64`

## 5.1.0 - 2017-02-17

- add `fullUrl` method

## 5.0.2 - 2017-02-07

- fix loading manipulations from the db

## 5.0.1 - 2017-02-06

- fix for `getFirstMediaUrl()` always returning the url for the first conversion 

## 5.0.0 - 2017-02-06 

- add `toMediaLibraryOnCloudDisk`
- image generators may now be specified in the config file
- use json columns for `manipulations` and `custom_properties`
- refactor all functions in `FileCannotBeAdded` to their own exception classes
- rename config file from `laravel-medialibrary` to `medialibrary`
- remove `toCollection` and `toCollectionOnDisk` and `toMediaLibraryOnDisk`
- replace dependency on `spatie/laravel-glide` by `spatie/image`
- mime types will now be stored in the database so they can be queried even if files are stored on external filesystems
- rename `Spatie\MedialibraryFilesystemInterface` to `Spatie\Medialibrary\Filesystem\Filesystem`
- remove `withCustomProperties`, `getNestedCustomProperty`, `setNestedCustomProperty`, `forgetNestedCustomProperty` and `hasNestedCustomProperty`
- drop support for Lumen and anything below Laravel 5.4
- clean up all classes

**KNOWN BUG: loading manipulations from the db doesn't work in certain edge cases, fix incoming soon**

## 4.13.0 - 2017-01-30
- add `FilesystemInterface`

## 4.12.1 - 2017-01-27
- avoid unnecessary regeneration of conversions when saving a model

## 4.12.0 - 2017-01-23
- add support for Laravel 5.4
- drop support for Laravel 5.1

## 4.11.3 - 2017-01-20
- put files using `r` mode instead of `r+`

## 4.11.2 - 2017-01-17
- avoid creating / deleting temp dir if no conversions should be performed

## 4.11.1 - 2017-01-14
- fix bug in `setNewOrder`

## 4.11.0 - 2017-01-10
- added `hasNestedCustomProperty`, `getNestedCustomProperty`, `setNestedCustomProperty` and `forgetNestedCustomProperty` to use dot notation with custom properties
- renamed `removeCustomProperty` to `forgetCustomProperty` (`removeCustomProperty` still exists but is marked as deprecated)

## 4.10.3 - 2017-01-09
- fix for getting preloaded media in the wrong order

## 4.10.2 - 2016-12-17
- refactored the preloading of media

## 4.10.1 - 2016-12-12
- reduce amount of calls to `s3`

## 4.10.0 - 2016-12-09
- add `addCustomHeaders` function

## 4.9.5 - 2016-10-25
- improve returned values for `getUrl` methods

## 4.9.4 - 2016-10-21
- fix for image generators when using S3

## 4.9.3 - 2016-10-13
- the image generators wil now do their supported extensions check in a case insensitive manner

## 4.9.2 - 2016-10-06
- fixed bug where an exception would be raised when using S3

## 4.9.1 - 2016-09-23
- fix bug where urls to media would not be encoded anymore

## 4.9.0 - 2016-09-23
- introduced `ImageGenerators`

## 4.8.4 - 2016-09-15
- encode urls to media

## 4.8.3 - 2016-08-25
-  fix svg and pdf file path when performing conversions

## 4.8.2 - 2016-08-24
- made compatible with L5.3

## 4.8.1 - 2016-08-19
- fixed some files that had a wrong namespace

## 4.8.0 - 2016-08-07
- added thumbnail generation for video's
- added force option to the artisan commands

## 4.7.1 - 2016-08-02
- fixed the `src` format option when dealing with jpegs

## 4.7.0 - 2016-07-18

- added `mime` attribute on the `Media` model

## 4.6.0 - 2016-07-15

- added `removeCustomProperty` function

## 4.5.0 - 2016-07-09

- added `medialibrary:clean` command
- the `medialibrary:regenerate` will continue regenerating files even if a primary media file is missing

## 4.4.1 - 2016-07-08
- Fix regeneration command (see #260). It'll now properly regenerate files for all passed media id's

## 4.4.0 - 2016-07-06
- Add support for converting svg's

## 4.3.0 - 2016-06-23
- Add Lumen compatibility

## 4.2.1 - 2016-06-03
- Delete the conversion directory even when it is not underneath the media directory

## 4.2 - 2016-06-03
- Added the `src` option for the `fm` conversion parameter

## 4.1 - 2016-06-02
- Added `shouldDeletePreservingMedia`

## 4.0.1 - 2016-04-25
- Fixed queued jobs in Laravel 5.1

## 4.0.0 - 2016-04-13
- add support for Glide 1.0
- added `addMediaFromRequest` method
- small refactors

## 3.17.4 - 2016-04-12
- Only detect mimetype from file on local disks

## 3.17.3 - 2016-04-04
- Fixed an issue that occured when deleting models with media in some php versions

## 3.17.2 - 2016-03-25
- Fixed mistakes in the version constraints on the illuminate components

## 3.17.1 - 2016-03-24
- Improved the file type detection for files without an extension

## 3.17.0 - 2016-03-23
- Added support for `morphMap`

## 3.16.1 - 2016-03-20
- Improved the compatiblity with packages that hook into the `delete` method of an Eloquent model

## 3.16.0
- The `regenerate`-command now accepts an `ids`-option

## 3.15.0
- Added `medialibrary:clear` command

## 3.14.1
- Make migrations compatible with mysql's strict mode

## 3.14.0
- Added a `deletePreservingMedia`-function that will delete the model but not remove the associated files

## 3.13.4
- Use `config_path` helper in ServiceProvider to allow easier integration in Lumen

## 3.13.3
- Recognize gifs as images

## 3.12.2
- Removed support for laravel-glide v3
- Added missing `InvalidNewOrder`-exception

## 3.12.1
*Important node: there is a bug in this version that prevents the creation
of derived files*
- Add support for laravel-glide v3

## 3.12.0
- Add configurable headers when uploading media to a remote disk

## 3.11.3
- use database_path when publishing migrations

## 3.11.2
- Fixed the processing a file name with special characters

## 3.11.1
- Remove adding .gitignore files

## 3.11.0
- Accept Symfony\Component\HttpFoundation\File\File-object when adding files

## 3.10.2
- Fixed mime-type errors when using the local filesystem

## 3.10.1
- Fixed the event names to make them more readable `CollectionHasBeenCleared`, `ConversionHasBeenCompleted`, `MediaHasBeenAdded`

## 3.10.0
- Added `CollectionClearedEvent`, `ConversionCompleteEvent`, `MediaAddedEvent`

## 3.9.2
- Fixed an issue where a model would not regenerate manipulations after changing manipulations on media

## 3.9.1
- Fix bug when using a custom UrlGenerator class

## 3.9.0
- Added PathGenerator

**This version contains a bug when using a custom UrlGenerator, please upgrade to 3.9.1**

## 3.8.0
- Added ability to add media from a url

$media = $this->testModel->addMediaFromUrl($url)
## 3.7.3
- `clearMediaCollection` is now chainable

## 3.7.2
- Add mimetype when putting a file on a disk.

## 3.7.1
- Fix generation of local url's on non-unix hosts

## 3.7.0
- Added `setCustomProperty`-method on Media

## 3.6.0
- Added `withProperties` and `withAttributes` methods

## 3.5.1
- Bugfix: `HasMediaTrait::updateMedia` now also updates custom properties. It also updates the order column starting at 1 instead of 0 (behaves the same as the sortable trait)

## 3.5.0
- Added the ability to provide a default value fallback to the `getCustomProperty` method

## 3.4.0
- Added support for using a custom model

## 3.3.1
- Fixed a bug where conversions would always be performed on the default queue

## 3.3.0
- Added `hasCustomProperty`- and `getCustomProperty`-convenience-methods

## 3.2.5
- Allow 0 for `x` and `y` parameters in `setRectangle`

## 3.2.4
- Removed dependency on spatie/eloquent-sortable

## 3.2.3
- Add index to morphable fields in migration which could improve performance.
- Remove unnecessary query when adding a file

## 3.2.2
- Fixes tests

## 3.2.1
- Add index to morphable fields in migration which could improve performance.
NOTE: if you started out using this version, the tests will be broken. You should make sure 
model_id and model_type are nullable in your database.

## 3.2.0
- Added functions to get a path to a file in the media library

## 3.1.5
- Avoid creating empty conversions-directories

## 3.1.4
- Fixed a bug where chaining the conversion convenience methods would not give the right result

## 3.1.3
- Fixed a bug where getByModelType would return null

## 3.1.2
- Images and pdf with capitalized extensions will now be recognized

## 3.1.1
- Fixed: a rare issue where binding the command would fail

## 3.1.0
- Added: methods to rename the media object and file name before adding a file to the collection

## 3.0.1
- Fixed: `updateMedia` now returns updated media

## 3.0.0
- Replaced `addMedia` by a fluent interface
- Added the ability to store custom properties on a media object
- Added support for multi-filesystem libraries
- `getMedia` will now return all media regardless of collection
- `hasMedia` will count all media regardless of collection
- Uploads can now be processed directly when importing a file
- Renamed various classes to better reflect their functionality

## 2.3.0
- Added: hasMedia convenience method

## 2.2.3
- Fixed: when renaming file_name on a media object the orginal file gets renamed as well

## 2.2.2
- Fixed: use FQCN for facades instead of using the aliases

## 2.2.1
- Fixed an issue where too much queries were executed

## 2.2.0
- Added `hasMediaWithoutConversions`-interface

## 2.1.5
- Fixes a bug where a valid UrlGenerator would not be recognized

## 2.1.4
- Fixes a bug where an exception would be thrown when adding a pdf on systems without Imagick installed

## 2.1.3
- Fixes some bugs where files would not be removed when deleting a media-object

## 2.1.2
- Require correct version of spatie/string

## 2.1.1
- Bugfix: correct typehint in HasMediaTrait

## 2.1.0
- Added some convenience methods for some frequent used manipulations

## 2.0.1
- fix bug in regenerate command

## 2.0.0
This version is a complete rewrite. Though there are lots of breaking changes most features of v1 are retained. Notable new functions:
- filesystem abstraction:  associated files can be stored on any filesystem Laravel 5's filesystem allows. So you could for instance store everything on S3.
- thumbnails can now be generated for pdf files
- registering conversions has been made more intuïtive
- it's now very easy to add custom logic to generate urls
- images can be manipulated per media object

## 1.6.2
- Bugfix: prevent migration from being published multiple times

## 1.6.1
- Small bugfixes

## 1.6.0
- Added: `Spatie\MediaLibrary\Models\Media::getHumanReadableFileSize()`

## 1.5.6
- Bugfix: make compatible with Laravel 5.1

## 1.5.5
- Bugfix: Renamed the boot method of MedialibraryModeltrait so it plays nice with the boot method of 
other traits and the base model.

## 1.5.4
- Feature: The `profile` parameter in `Media::getUrl()` and `MediaLibraryModelTrait::getUrl()` is now optional. On null, it retrieves the original file's url.
- Bugfix: `Media::getOriginalUrl()` now returns the correct url.

## 1.5.3
- Bugfix: Removed unnecessary static methods from `MediaLibraryModelInterface`

## 1.5.0
- Added a method to remove all media in a collection.

## 1.1.4
- Fixed a bug where not all image profiles would be processed
- Added `getImageProfileProperties()`to interface

## 1.1.3
- Create the medialibrary directory if it does not exist

## 1.1.2
- Files without extensions are now allowed

## 1.1.1
- Added check to make sure the file that must be added to the medialibrary exists

## 1.1.0
- Added option to specify the name of the queue that should be used to create image manipulations

## 1.0.0
- initial release
