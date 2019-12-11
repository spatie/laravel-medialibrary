# Changelog

## [7.15.1](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.15.0...Okipa:7.15.1)

2019-12-11

* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.16.1 release.

## [7.15.0](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.14.5...Okipa:7.15.0)

2019-12-04

* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.16.0 release.

## [7.14.5](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.14.4...Okipa:7.14.5)

2019-11-27

* The extensions detection from mime types from the `Spatie\MediaLibrary\HasMedia\HasMediaTrait::extensionsFromMimeTypes()` method is now executed by `Symfony\Component\Mime\MimeTypes::getExtensions()` (which is far more reliable).
* The `mimes` validation is now executed before the `mimetypes` validation, in order to return a more comprehensible error for end user in case of wrong uploaded file type.

## [7.14.4](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.14.3...Okipa:7.14.4)

2019-11-25

* Fixed mimes extraction from mimes types, in order to remove the duplicated mimes during the constraints and legend generation.

## [7.14.3](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.14.2...Okipa:7.14.3)

2019-10-17

* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.14.2 release.

## [7.14.2](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.14.1...Okipa:7.14.2)

2019-10-15

* Fixed the translations publication and overriding as specified on the Laravel documentation : https://laravel.com/docs/packages#translations.

## [7.14.1](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.14.0...Okipa:7.14.1)

2019-09-27

* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.14.1 release.

## [7.14.0](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.13.4...Okipa:7.14.0)

2019-09-26

* Added mimes validation generation : https://laravel.com/docs/validation#rule-mimes
* Updated validation process order : mime types and mimes validations now happens before dimensions validation.
* :warning: The `->validationConstraints()` method does now return an array, rather than a string before.
* :warning: Removed the `CollectionNotFound` exception in order to follow the base package behaviour.
* :warning: Removed the `ConversionsNotFound` exception in order to follow the base package behaviour.
* :warning: Replaced the `__('medialibrary.constraint.mimeTypes')` translation by `trans_choice('medialibrary.constraint.types')` translation, in order to provide clearer legends.

## [7.13.4](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.13.3...Okipa:7.13.4)

2019-09-25

* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.14.0 release.
  * you now have to set `version_urls` to `true` in the config file in order to have your image urls versioned.

## [7.13.3](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.13.2...Okipa:7.13.3)

2019-09-25

* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.13.0 release.
* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.12.4 release.
* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.12.3 release.

## [7.13.2](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.13.1...Okipa:7.13.2)

2019-09-24

* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.12.2 release.

## [7.13.1](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.13.0...Okipa:7.13.1)

2019-09-13

* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.12.1 release.

## [7.13.0](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.12.0...Okipa:7.13.0)

2019-09-04

* Implemented https://github.com/spatie/laravel-medialibrary/releases/tag/7.12.0 release.

## [7.12.0](https://github.com/Okipa/laravel-medialibrary-ext/compare/7.11.0...Okipa:7.12.0)

2019-08-27

* Added automatic image file name version for cache busting when `config('medialibrary.image_name_versioning')` is set to true.
* Fixed missing translations loading in service provider.
* Implemented `spatie/laravel-medialibrary:7.10.1` release.

## [7.11.0](https://github.com/Okipa/laravel-medialibrary-ext/releases/tag/7.11.0)

2019-08-27

* First extension release.
