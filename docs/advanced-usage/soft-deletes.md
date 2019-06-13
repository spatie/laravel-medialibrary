---
title: Soft deleting
---

The medialibrary has support for [soft deleting](https://laravel.com/docs/eloquent#soft-deleting).
 
It's pretty straightforward. When you soft delete a model with `$model->delete()` the media will not be removed. When you force delete a model with `$model->forceDelete()` the media will be removed.
