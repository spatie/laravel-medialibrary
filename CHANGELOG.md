# Changelog

All notable changes to `laravel-medialibrary` will be documented in this file

## 9.4.1 - 2020-12-16

- fixed generated conversions race-condition (#2235)

## 9.4.0 - 2020-12-02

- add `moves_media_on_update` config value

## 9.3.0 - 2020-11-30

- add `generate_thumbnails_for_temporary_uploads` config value

## 9.2.0 - 2020-11-26

- add `enable_temporary_uploads_session_affinity` config value

## 9.1.7 - 2020-11-24

- add unique index to UUID column (#2197)

## 9.1.6 - 2020-11-24

- fixes incompatible return types error caused by PHPStorm's inability to resolve self (#2198)

## 9.1.5 - 2020-11-22

- fix custom file names for media library pro

## 9.1.4 - 2020-11-19

- automatically delete conversion jobs for deleted models (#2191)

## 9.1.3 - 2020-11-12

- improve optimizer defaults

## 9.1.2 - 2020-11-11

- add `original_url` to `MediaCollection`.

## 9.1.1 - 2020-11-05

- allow media collection to work with media library pro

## 9.1.0 - 2020-11-04

- allow image generators to accept config (#2156)

## 9.0.1 - 2020-10-30

- do not enable vapor uploads by default

## 9.0.0 - 2020-10-30

- add support for [Media Library Pro](https://medialibrary.pro)
- names of the generated conversions will now be put in a dedicated `generated_conversions` on media
- responsive image files can now be named using the `file_namer` key in the `media-library` config file (#2114)

## 8.10.1 - 2020-10-05

- add `queue_conversions_by_default` to config file

## 8.9.3 - 2020-10-03

- fix responsive images

## 8.9.2 - 2020-10-02

- improve responsive image inline script (#2032)

## 8.9.1 - 2020-10-02

- missing $loadingAttributeValue test in image view (#2082)

## 8.9.0 - 2020-09-30

- add support to include `ResponsiveImages` based on condition (#2036)

## 8.8.0 - 2020-09-30

- allow to change the way the images are being downloaded (#2054)

## 8.7.5 - 2020-09-30

- fix for default lazy="auto" value (#2081)

## 8.7.4 - 2020-09-30

- fixed conversions when disk != conversions_disk (#2080)

## 8.7.3 - 2020-09-28

- fix file deletion problem (#2073)

## 8.7.2 - 2020-09-20

- allow Guzzle 7 in dev-deps

## 8.7.1 - 2020-09-08

- add support for Laravel 8

## 8.7.0 - 2020-09-04

- add `toMediaLibrary`

## 8.6.0 - 2020-08-25

- add `useZipOptions`

## 8.5.2 - 2020-08-25

- fix for custom zip path (#2016)

## 8.5.1 - 2020-08-24

- keep sizes 1px if width is 0px (#1993)

## 8.5.0 - 2020-08-06

- add method to get registered media collections (#1976)

## 8.4.1 - 2020-08-03

- add `addMediaFromString`

## 8.4.0 - 2020-08-03

- add `addFromString`

## 8.3.3 - 2020-06-30

- fix responsive image urls when conversions are stored on different disk. (#1944)

## 8.3.2 - 2020-06-22

- report an error when it can't delete a directory (#1938)

## 8.3.1 - 2020-06-22

- improve handling of file names with special characters (#1937)


## 8.3.0 - 2020-06-11

- added `Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection`

## 8.2.9 - 2020-06-08

- changed `freads` to `fgets`  as per issue #812 (#1909)

## 8.2.8 - 2020-05-16

- when generating responsive images the quallity option of the original will be used (#1881)

## 8.2.7 - 2020-05-12

- Unset loaded 'media' relation during updateMedia() (#1878)

## 8.2.6 - 2020-05-10

- revert slash because of Vapor errors (#1869)

## 8.2.5 - 2020-05-07

- set the media table name explicitly to `media` to avoid failure caused by doctrine/inflictor now using `medias` as the plural

## 8.2.4 - 2020-05-01

- fix for when using custom model (#1857)

## 8.2.3 - 2020-04-29

- fixed duplicated path prefix for temporary urls (#1853)

## 8.2.2 - 2020-04-29

- fixed duplicated `/` in paths (#1854)

## 8.2.1 - 2020-04-20

- do not allow local files to be used in `addMediaFromUrl`

## 8.2.0 - 2020-04-14

- add page number support for Pdf image generator (#1829)

## 8.1.0 - 2020-04-07

- add support for `zip_filename_prefix` in custom properties

## 8.0.8 - 2020-04-02

- allow ZipStream 2.0 (#1812)

## 8.0.7 - 2020-03-03

- solve error when using flysystem-cached-adapter (#1803)

## 8.0.6 - 2020-03-24

- fix comment in config file

## 8.0.5 - 2020-03-24

- Use `float` for `extractVideoFrameAtSecond` (#1794)

## 8.0.4 - 2020-03-24

- update php-ffmpeg to ^0.15

## 8.0.3 - 2020-03-18

- add callable filter support to `getFirstMedia()` (#1781)

## 8.0.1 - 2020-03-11

- set conversion disk when adding media from remote (#1764)

## 8.0.0 - 2020-03-09

- added `uuid` on `media` table
- an empty string is now a proper collection name. `getMedia('')` will not return media from the default collection anymore (#1697).
- add the ability to store conversions on a separate disk
- simplify URL generation. You can now just use the `root` and `url` properties on a configured disk
- spatie/pdf-to-image is now a suggestion dependency, removing the need for always having to install ext-imagick
- added `shouldMatchBothExtensionsAndMimeTypes` to `Spatie\MediaLibrary\ImageGenerators\BaseGenerator`
- added progress bar on the clean command (#1623)
- the `UrlGenerator` interface now contains all required methods (#1656)
- use PHP 7.4 features where possible
- added support for the `loading` attribute (#1667)
- conversion files can now be named using the `conversion_file_namer` key in the `media-library` config file (#1636)
- improved naming of classes and namespaces.

To learn how to upgrade, take a look in UPGRADING.md

## 7.19.3 - 2020-03-09

- fix responsive images extension (#1752)

## 7.19.2 - 2020-03-04

- revert changes in 7.19.1

## 7.19.1 - 2020-03-04

- Update S3 url generator to use media disk (#1755)

## 7.19.0 - 2020-03-03

- add support for Laravel 7

## 7.18.3 - 2020-02-19

- allow `image_driver` config to be set via .env #1738

## 7.18.2 - 2020-01-25

- add support for Laravel 7

## 7.18.1 - 2019-11-25

- revert of pull request #1604 because zip files could not be opened (#1660)

## 7.18.0 - 2020-01-05

- add `withResponsiveImages()` to custom collection (#1681)

## 7.17.1 - 2019-12-15

- fix custom disk url giving invalid urls (#1653)

## 7.17.0 - 2019-12-15

- added diskName on copy and move methods in media model (#1666)

## 7.16.2 - 2019-12-15

- correctly use the media item's disk when removing responsive images (#1668)

## 7.16.1 - 2019-12-11

- escape responsive URL - Fix issue #1659 (#1661)

## 7.16.0 - 2019-12-02

- add ability to upload files from a non-local disk

## 7.15.0 - 2019-11-25

- bumped dependency of zipstream-php
- fix so when creating a zip files are read only once (#1604)

## 7.14.2 - 2019-10-16

- fix so files without extension could be added

## 7.14.1 - 2019-09-26

- generate the name of the converted file in one place (#1577)

## 7.14.0 - 2019-09-25

- add a config option to version urls (#1569)

## 7.13.0 - 2019-09-25

- add a way to define accepted mime types (#1570)

## 7.12.4 - 2019-09-25

- tidy up `getFallbackMediaUrl` and `getFallbackMediaPath`

## 7.12.3 - 2019-09-25

- fix media stream not working (#1571)

## 7.12.2 - 2019-09-24

- fix upload for very large files

## 7.12.1 - 2019-09-12

- remove imagick requirement

## 7.12.0 - 2019-09-04

- add support for Laravel 6

## 7.10.1 - 2019-08-28

- do not export docs

## 7.10.0 - 2019-08-21

- add `onlyKeepLatest` on `MediaCollection`

## 7.9.0 - 2019-08-07

- `FileAdder` now is macroable

## 7.8.2 - 2019-07-31

- make sure `CollectionHasBeenCleared` will be called when using `clearMediaCollectionExcept`

## 7.8.1 - 2019-07-31

- fix for custom manipulations not getting appllied to all relevant conversions with the same name

## 7.8.0 - 2019-07-31

- make media collection macroable

## 7.7.0 - 2019-07-27

- add `useFallbackUrl` and `useFallbackPath` to media collections

## 7.6.9 - 2019-07-22

- avoid using deprecated str and arr functions

## 7.6.8 - 2019-07-22

- fix for S3 Responsive Image URL Generator not using root

## 7.6.7 - 2019-07-22

- allow stable version of zipstream

## 7.6.6 - 2019-07-22

- fix absolute references to media.id

## 7.6.5 - 2019-07-16

- Support `jpeg` in `\Spatie\MediaLibrary\Conversion\Conversion::getResultExtension`

## 7.6.4 - 2019-07-15

- Add imagick as required extension, because of nested dependencies (#1480)

## 7.6.3 - 2019-07-12

- `--only-missing` for queued conversions (#1465)

## 7.6.2 - 2019-07-11

- Allow Uploading multiple files under the same name using array name (#1471)

## 7.6.0 - 2019-02-27

- drop support for PHP 7.1

## 7.5.6 - 2019-02-19

- add support for Laravel 5.8

## 7.5.5 - 2019-01-05

- avoid exception when getting a video frame that does not exist

## 7.5.4 - 2019-01-04

- only set `custom_headers` property if explicitly set

## 7.5.3 - 2019-01-03

- use absolute urls for responsive image sources
- fix sortable

## 7.5.2 - 2018-10-19

- fix for issue #1277

## 7.5.1 - 2018-09-17

- fix support for Lumen

## 7.5.0 - 2018-09-10

- add rate limiting to clean command

## 7.4.3 - 2018-09-10

- fix for determining extension for non-image filetypes

## 7.4.2 - 2018-09-05

- fix a bug in clean command when no responsive images were generated

## 7.4.1 - 2018-08-24

- add support for Laravel 5.7

## 7.4.0 - 2018-08-13

- allow the job classes to be overridden in the config file

## 7.3.12 - 2018-07-30

- make sure previews responsive images db entries get cleaned up before regenerating

## 7.3.11 - 2018-07-27

- add `$copiedOriginalFile` to the `ConversionWillStart` event

## 7.3.10 - 2018-06-16

- fix for multiple files with the same filename in one ZIP archive
- fix `markAsConversionGenerated`: disable model events when saving extra properties in Media::updated event

## 7.3.9 - 2018-06-16

**do not use - broken**

- fix `markAsConversionGenerated`

## 7.3.8 - 2018-05-15

- fix `ids` option of `RegenerateCommand`

## 7.3.7 - 2018-05-15

- bugfix around responsive images

## 7.3.6 - 2018-05-15

- add support from `webp`

## 7.3.5 - 2018-05-08

- fix bug where `addMediaFromUrl` would not work if the file contained a space

## 7.3.4 - 2018-05-07

- proper check and tests on forced deletion with soft delete models.

## 7.3.3 - 2018-05-04

- add dev dependency on pdo SQLite to prevent confusing errors.

## 7.3.2 - 2018-05-04

- fix #1076

## 7.3.1 - 2018-05-02

- fix custom properties not saved on copy (#1073)

## 7.3.0 - 2018-04-30

- Add `hasGeneratedConversion`

## 7.1.8 - 2018-04-06

- avoid removing the file when the model uses `SoftDeletes`

## 7.1.7 - 2018-04-24

- improve checking applied traits on the Media model

## 7.1.6 - 2018-04-16

- fix `ffprobe` path

## 7.1.5 - 2018-04-13

- always use the correct image driver.

## 7.1.4 - 2018-04-13

- ease `maennchen/zipstream-php` requirements

## 7.1.3 - 2018-03-30

- Fix for renaming files when not all conversions are present
- Fix bugs when working with remote filesystems

## 7.1.2 - 2018-03-22

- fix a typo in `medialibrary.disk_name`.

## 7.1.0 - 2018-03-22

- `Filesystem` interface removed.
- rename `Filesytem::renameFile(Media $media, string $oldFileName)` to `Filesystem::syncFileNames(Media $media)`
- The `default_filesystem` config key has been changed to `disk_name`.

## 7.0.6 - 2018-03-22

- fix publishing views

## 7.0.5 - 2018-03-22

- fix for adding remote files with no name

## 7.0.4 - 2018-03-21

- fix responsive images rendering of conversions

## 7.0.3 - 2018-03-21

- add null fallback when placeholder SVG isn't rendered yet (#967)
- add ResponsiveImagesGenerated event

## 7.0.2 - 2018-03-21

- support custom headers for conversions (#868)

## 7.0.0 - 2018-03-17

- added support for responsive images
- added `MediaCollections`
- added single file collections
- added `ZipStreamResponse`

- added `move` and `copy` methods on `Media`

- file names will be lowercased when adding them to the media library
- the names of converted images will now start with the name of the original file

- dropped support for soft deletes
- removed distinction between `HasMedia` and `HasMediaConversions`
- dropped support for PHP 7.0

- `ffmpeg_binaries` renamed to `ffmpeg_path`, `ffprobe_binaries` renamed to `ffprobe_path`

## 6.9.0 - 2018-03-04

- add wildcard manipulations

## 6.8.0 - 2018-03-03

- add `withManipulations` to `FileAdder`

## 6.7.0 - 2018-03-02

- add support for `root` config key for s3 disks.

## 6.6.9 - 2018-02-08

- add support for L5.6

## 6.6.8 - 2018-02-05

- change the directory deletion order

## 6.6.7 - 2018-01-07

- use better default for s3 domain

## 6.6.6 - 2017-12-30

- fix download error


## 6.6.5 - 2017-12-30

- make returning media in controllers always download the associated file

## 6.6.4 - 2017-12-24

- update `spatie/image` dep

## 6.6.3 - 2017-11-28

- fix clearing entire media collection except a single media instance

## 6.6.2 - 2017-11-07

- improve config comments

## 6.6.1 - 2017-11-02

- fixed the spelling of the `getFirstTemporaryUrl` method

## 6.6.0 - 2017-11-02

- add `getFirstTemporaryUrl`

## 6.5.0 - 2017-10-24

- add `only-missing` and `only` options to the `media-library:regenerate` command

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
- rename `Spatie\MediaLibraryFilesystemInterface` to `Spatie\MediaLibrary\Filesystem\Filesystem`
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

- added `media-library:clean` command
- the `media-library:regenerate` will continue regenerating files even if a primary media file is missing

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
- Added `media-library:clear` command

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
- Fixed an issue where too many queries were executed

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
- Bugfix: Renamed the boot method of MediaLibraryModeltrait so it plays nice with the boot method of
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
- Create the media library directory if it does not exist

## 1.1.2
- Files without extensions are now allowed

## 1.1.1
- Added check to make sure the file that must be added to the media library exists

## 1.1.0
- Added option to specify the name of the queue that should be used to create image manipulations

## 1.0.0
- initial release
