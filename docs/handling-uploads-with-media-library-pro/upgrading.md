---
title: Upgrading
weight: 10
---

## Upgrading

This file contains instructions on how to upgrade to another major version of the package.

## From v2 to v3

v3 was created to solve security issues. If not secured properly, the controllers registered by the `mediaLibrary` route macro were too open.

In v3 we hardened security by:

- only allowing certain file types by default
- use rate limiting by default
- encourage users to apply authentication

Codewise, v3 contains no breaking changes, so upgrading should be easy. 

To learn how to customize the security default review the [Add the route macro section](/docs/laravel-medialibrary/v10/handling-uploads-with-media-library-pro#add-the-route-macro)

## From v1 to v2

No changes to the public API were made. Support for PHP 7 was dropped.
You should be able to upgrade without making any changes.
