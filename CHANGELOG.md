#Changelog

All notable changes to `laravel-medialibrary` will be documented in this file

##3.18.0 - 2016-06-02
-added `shouldDeletePreservingMedia`

##3.17.4 - 2016-04-12
- Only detect mimetype from file on local disks

##3.17.3 - 2016-04-04
- Fixed an issue that occured when deleting models with media in some php versions

##3.17.2 - 2016-03-25
- Fixed mistakes in the version contraints on the illuminate components

##3.17.1 - 2016-03-24
- Improved the file type detection for files without an extension

##3.17.0 - 2016-03-23
- Added support for `morphMap`

##3.16.1 - 2016-03-20
- Improved the compatiblity with packages that hook into the `delete` method of an Eloquent model

##3.16.0
- The `regenerate`-command now accepts an `ids`-option

##3.15.0
- Added `medialibrary:clear` command

##3.14.1
- Make migrations compatible with mysql's strict mode

##3.14.0
- Added a `deletePreservingMedia`-function that will delete the model but not remove the associated files

##3.13.4
- Use `config_path` helper in ServiceProvider to allow easier integration in Lumen

##3.13.3
- Recognize gifs as images

##3.12.2
- Removed support for laravel-glide v3
- Added missing `InvalidNewOrder`-exception

##3.12.1
*Important node: there is a bug in this version that prevents the creation
of derived files*
- Add support for laravel-glide v3

##3.12.0
- Add configurable headers when uploading media to a remote disk

##3.11.3
- use database_path when publishing migrations

##3.11.2
- Fixed the processing a file name with special characters

##3.11.1
- Remove adding .gitignore files

##3.11.0
- Accept Symfony\Component\HttpFoundation\File\File-object when adding files

##3.10.2
- Fixed mime-type errors when using the local filesystem

##3.10.1
- Fixed the event names to make them more readable `CollectionHasBeenCleared`, `ConversionHasBeenCompleted`, `MediaHasBeenAdded`

##3.10.0
- Added `CollectionClearedEvent`, `ConversionCompleteEvent`, `MediaAddedEvent`

##3.9.2
- Fixed an issue where a model would not regenerate manipulations after changing manipulations on media

##3.9.1
- Fix bug when using a custom UrlGenerator class

##3.9.0
- Added PathGenerator

**This version contains a bug when using a custom UrglGenerator, please upgrade to 3.9.1**

##3.8.0
- Added ability to add media from a url

$media = $this->testModel->addMediaFromUrl($url)
##3.7.3
- `clearMediaCollection` is now chainable

##3.7.2
- Add mimetype when putting a file on a disk.

##3.7.1
- Fix generation of local url's on non-unix hosts

##3.7.0
- Added `setCustomProperty`-method on Media

##3.6.0
- Added `withProperties` and `withAttributes` methods

##3.5.1
- Bugfix: `HasMediaTrait::updateMedia` now also updates custom properties. It also updates the order column starting at 1 instead of 0 (behaves the same as the sortable trait)

##3.5.0
- Added the ability to provide a default value fallback to the `getCustomProperty` method

##3.4.0
- Added support for using a custom model

##3.3.1
- Fixed a bug where conversions would always be performed on the default queue

##3.3.0
- Added `hasCustomProperty`- and `getCustomProperty`-convenience-methods

##3.2.5
- Allow 0 for `x` and `y` parameters in `setRectangle`

##3.2.4
- Removed dependency on spatie/eloquent-sortable

##3.2.3
- Add index to morphable fields in migration which could improve performance.
- Remove unnecessary query when adding a file

##3.2.2
- Fixes tests

##3.2.1
- Add index to morphable fields in migration which could improve performance.
NOTE: if you started out using this version, the tests will be broken. You should make sure 
model_id and model_type are nullable in your database.

##3.2.0
- Added functions to get a path to a file in the media library

##3.1.5
- Avoid creating empty conversions-directories

##3.1.4
- Fixed a bug where chaining the conversion convenience methods would not give the right result

##3.1.3
- Fixed a bug where getByModelType would return null

##3.1.2
- Images and pdf with capitalized extensions will now be recognized

##3.1.1
- Fixed: a rare issue where binding the command would fail

##3.1.0
- Added: methods to rename the media object and file name before adding a file to the collection

##3.0.1
- Fixed: `updateMedia` now returns updated media

##3.0.0
- Replaced `addMedia` by a fluent interface
- Added the ability to store custom properties on a media object
- Added support for multi-filesystem libraries
- `getMedia` will now return all media regardless of collection
- `hasMedia` will count all media regardless of collection
- Uploads can now be processed directly when importing a file
- Renamed various classes to better reflect their functionality

##2.3.0
- Added: hasMedia convenience method

##2.2.3
- Fixed: when renaming file_name on a media object the orginal file gets renamed as well

##2.2.2
- Fixed: use FQCN for facades instead of using the aliases

##2.2.1
- Fixed an issue where too much queries were executed

##2.2.0
- Added `hasMediaWithoutConversions`-interface

##2.1.5
- Fixes a bug where a valid UrlGenerator would not be recognized

##2.1.4
- Fixes a bug where an exception would be thrown when adding a pdf on systems without Imagick installed

##2.1.3
- Fixes some bugs where files would not be removed when deleting a media-object

##2.1.2
- Require correct version of spatie/string

##2.1.1
- Bugfix: correct typehint in HasMediaTrait

##2.1.0
- Added some convenience methods for some frequent used manipulations

##2.0.1
- fix bug in regenerate command

##2.0.0
This version is a complete rewrite. Though there are lots of breaking changes most features of v1 are retained. Notable new functions:
- filesystem abstraction:  associated files can be stored on any filesystem Laravel 5's filesystem allows. So you could for instance store everything on S3.
- thumbnails can now be generated for pdf files
- registering conversions has been made more intuïtive
- it's now very easy to add custom logic to generate urls
- images can be manipulated per media object

##1.6.2
- Bugfix: prevent migration from being published multiple times

##1.6.1
- Small bugfixes

##1.6.0
- Added: `Spatie\MediaLibrary\Models\Media::getHumanReadableFileSize()`

##1.5.6
- Bugfix: make compatible with Laravel 5.1

##1.5.5
- Bugfix: Renamed the boot method of MedialibraryModeltrait so it plays nice with the boot method of 
other traits and the base model.

##1.5.4
- Feature: The `profile` parameter in `Media::getUrl()` and `MediaLibraryModelTrait::getUrl()` is now optional. On null, it retrieves the original file's url.
- Bugfix: `Media::getOriginalUrl()` now returns the correct url.

##1.5.3
- Bugfix: Removed unnecessary static methods from `MediaLibraryModelInterface`

##1.5.0
- Added a method to remove all media in a collection.

##1.1.4
- Fixed a bug where not all image profiles would be processed
- Added `getImageProfileProperties()`to interface

##1.1.3
- Create the medialibrary directory if it does not exist

##1.1.2
- Files without extensions are now allowed

##1.1.1
- Added check to make sure the file that must be added to the medialibrary exists

##1.1.0
- Added option to specify the name of the queue that should be used to create image manipulations

##1.0.0
- initial release
