#Changelog

All Notable changes to `laravel-medialibrary` will be documented in this file

##1.5.6
- Bugfix: make compatible with Laravel 5.1

##1.5.5
- Bugfix: Renamed the boot method of MedialibraryModeltrait so it plays nice with the boot method of 
other traits and the base model.

##1.5.4
- Feature: The `profile` parameter in `Media::getUrl()` and `MediaLibraryModelTrait::getURL()` is now optional. On null, it retrieves the original file's url.
- Bugfix: `Media::getOriginalURL()` now returns the correct url.

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
