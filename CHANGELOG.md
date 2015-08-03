#Changelog

All Notable changes to `laravel-medialibrary` will be documented in this file

##3.0.0
- Replaced `addMedia` by a fluent interface
- Added the ability to store custom properties on a media object
- Added support for multi-filesystem medialibraries
- `getMedia` will now return all media regardless of collection
- `hasMedia` will count all media regardless of collection

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
