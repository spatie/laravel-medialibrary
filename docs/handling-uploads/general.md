---
title: General
weight: 1
---

Media Library Pro offers [Blade](TODO add link to Blade components page), [Vue](TODO) and [React](TODO) components that make it easy to upload files to the media library. You can only make use this functionality when you have a license for [Media Library Pro](https://medialibrary.pro).

You can use these components in regular forms. Here's an example using the `x-media-library-upload` blade component.

```html
<form method="POST">
    @csrf

    <input id="name" name="name">

    <x-media-library-upload name="avatar" />

    <button type="submit">Submit</button>
</form>
```

The `x-media-library-upload`, and equivalent Vue and React components, will take care of the upload. After a file has been uploaded it will be stored as a temporary upload. In case there are validation errors when submitting the form, the `x-media-library-upload` will display the temporary upload. There's no need for the user to upload the file again.

In the controller that handles the form submission, you can add transfer the temporary upload to an Eloquent model like this. This code works Blade, Vue and React components

```php
public function store(StoreMultipleUploadsRequest $request)
{
    // ... retrieve your model
    
    $yourModel
       ->addMediaFromMediaLibraryRequest($request, 'avatar')
       ->toMediaCollection('avatar')
    
    // ... redirect the user somewhere
}
```


