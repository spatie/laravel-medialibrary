---
title: Processing uploads on the server
weight: 3
---

All Blade, Vue and React components communicate with the server in the same way. After a user selects one or more files, they're immediate sent to the server and stored as temporary uploads. When the parent form is submitted, the media items can be attached to a model.

## Are you a visual learner?

This video shows you how Media Library Pro uses temporary uploads under the hood.

<iframe width="560" height="315" src="https://www.youtube.com/embed/mtQFZu72CCo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/videos/discovering-laravel-media-library).

## Enabling temporary uploads

Plain HTML file `<input>`s have two major shortcomings: they only upload the file when the form is submitted, and they're unable to remember the file when a form fails to submit. Temporary uploads solve both these problems.

When a user selects or drops a file in one of the Media Library components, it gets uploaded to the server immediately. Problem number 1 solved! 

If the form submission fails later on, Media Library will pass down the previously added temporary upload objects so it can prefill the component with the previously uploaded files. Problem number 2 solved too!

To set up temporary uploads, register the temporary uploads route with our handy macro.

```php
// Probably routes/web.php

Route::mediaLibrary();
```

This will register a route at `/media-library-pro/uploads`


### Enabling Vapor support

If you will use React or Vue components to handle uploads you must set the `enable_vapor_uploads` key in the `media-library` config file to `true`. When enabling this option, a route will be registered that will enable
the Media Library Pro Vue and React components to move uploaded files in an S3 bucket to their right place.

With the config option enable, the `Route::mediaLibrary();` will register a route at `/media-library-pro/post-s3
 instead of `/media-library-pro/uploads`.

### Customizing the upload URL

You can customize the upload url by passing a base url to the macro.

```php
// Probably routes/web.php

Route::mediaLibrary('my-custom-url');
```

This will register a route at `/my-custom-url/uploads`

## Setting up the view & controller

After a user has added files and they've been stored as temporary uploads, the user will submit the form. At this point the form request will hit one of your application's controllers. This is where you can permanently attach the file to your models.

To illustrate, we'll set up a little profile screen where a user may upload their avatar.

```php
// Back in routes/web.php
use App\Http\Controllers\ProfileController;

Route::get('profile', [ProfileController::class, 'edit']);
Route::post('profile', [ProfileController::class, 'store']);

Route::mediaLibrary();
```

The profile controller has a simple form that uses the Blade attachment component.

```blade
{{-- resources/views/profile.blade.php --}}

<x-media-library-attachment name="avatar" />
```

And, assuming you're familiar with the [basic usage](../basic-usage) of the Media Library, this is how we'd store the uploaded avatar on the user.

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController
{
    public function edit()
    {
        return view('profile', [$user => Auth::user()]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $user
            ->addMediaFromMediaLibraryRequest($request, 'avatar')
            ->toMediaCollection('avatar');
    }
}
```

## Validation

The `ProfileController` we built assumes users will only upload the exact file types we're looking for. Of course they won't! We need to validate the incoming media before attaching them to our models.

The Media Library components provide instant client-side validation. You'll read more about that in the component docs. First, we'll set up server-side validation.

To validate uploaded media, we'll use create a custom form request.

```diff
- public function store(Request $request)
+ public function store(ProfileRequest $request)
```
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\MediaLibraryPro\Rules\Concerns\ValidatesMedia;

class ProfileRequest extends FormRequest
{
    use ValidatesMedia;

    public function rules()
    {
        return [
            'images' => $this
                ->validateMultipleMedia()
                ->minItems(1)
                ->maxItems(5)
                ->extension('png')
                ->maxItemSizeInKb(1024)
                ->attribute('name', 'required')
        ];
    }
}
```

---

Every component will pass data in a key of a request. The name of that key is the name you passed to the `name` prop of any of the components.

```html 
// data will get passed via the `avatar` key of the request.

<x-media-library-attachment name="avatar" />
```

The content of that request key will be an array. For each file uploaded that array will hold an array with these keys.

- `name`: the name of the uploaded file
- `uuid`: the UUID of a `Media` model. For newly uploaded files that have not been associated to a model yet, the `Media` model will be associated with a `TemporaryUpload` model
- `order`: the order in which this item should be stored in a media collection.

## Validating responses

Even though the upload components do some validation of their own, we highly recommend always validating responses on the server as well.

You should handle validation in a form request. On the form request you should use the `Spatie\MediaLibraryPro\Rules\Concerns\ValidatesMedia` trait. This will give you access to the `validateSingleMedia` and `validateMultipleMedia` methods.

In this example we assume that a component was configured to use the `images` key of the request. We validate that there was at least one item uploaded, but no more than 5. Only `png`s that are up to 1MB in size are allowed. All images should have a name.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\MediaLibraryPro\Rules\Concerns\ValidatesMedia;

class MyRequest extends FormRequest
{
    use ValidatesMedia;

    public function rules()
    {
        return [
            'images' => $this
                ->validateMultipleMedia()
                ->minItems(1)
                ->maxItems(5)
                ->extension('png')
                ->maxItemSizeInKb(1024)
                ->attribute('name', 'required')
        ];
    }
}
```

If you are only allowing one uploaded file, you can use the `validateSingleMedia` in much the same way.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\MediaLibraryPro\Rules\Concerns\ValidatesMedia;

class MyRequest extends FormRequest
{
    use ValidatesMedia;

    public function rules()
    {
        return [
            'avatar' => $this
                ->validateSingleMedia()
                ->extension('png')
                ->maxItemSizeInKb(1024)
        ];
    }
}
```

These are the available validation methods on `validateSingleMedia() ` and`validateMultipleMedia`  

- `minSizeInKb($minSizeInKb)`: validates that a single upload is not smaller than the `$minSizeInKb` given
- `maxSizeInKb($maxSizeInKb)`: validates that a single upload is not greater than the `$minSizeInKb` given
- `extension($extension)`: this rule expects a single extension as a string or multiple extensions as an array. Under the hood, the rule will validate if the value has the mime type that corresponds with the given extension.
- `mime($mime)`: this rule expects a single mime type as a string or multiple mime types as an array.
- `itemName($rules)`: This rule accepts rules that should be used to validate the name of a media item.
- `customProperty($name, $rules)`: this rule accepts a custom property name and rules that should be used to validate the attribute

These rules can be used on `validateMultipleMedia`;

- `minTotalSizeInKb($maxTotalSizeInKb)`: validates that the combined size of uploads is not smaller than the `$minTotalSizeInKb` given
- `maxTotalSizeInKb($maxTotalSizeInKb)`: validates that the combined size of uploads is not greater than the `$maxTotalSizeInKb` given

### Validating attributes and custom properties

If you're [using custom properties](/docs/laravel-medialibrary/v9/handling-uploads-with-media-library-pro/handling-uploads-with-blade#using-custom-properties), you can validate them with the `customProperty` function. The first argument should be the name of the custom property you are validating. The second argument should be a string or an array with rules you'd like to use.

Here's an example where we validate `extra_propery` and `another_extra_property`.

```php
use Illuminate\Foundation\Http\FormRequest;
use Spatie\MediaLibraryPro\Rules\Concerns\ValidatesMedia;

class StoreLivewireCollectionCustomPropertyRequest extends FormRequest
{
    use ValidatesMedia;

    public function rules()
    {
        return [
            'name' => 'required',
            'images' => $this->validateMultipleMedia()
                ->customProperty('extra_field', 'required|max:50')
                ->customProperty('another_extra_property', ['required', 'max:50'])
        ];
    }
}
```

## Processing responses

After you've validated the response, you should persist the changes to the media library. The media library provides two methods for that: `syncFromMediaLibraryRequest` and `addFromMediaLibraryRequest`. Both these methods are available on all [models that handle media](/docs/laravel-medialibrary/v9/basic-usage/preparing-your-model).

### `addFromMediaLibraryRequest`

This method will add all media whose `uuid` is in the response to a media collection of a model. Existing media associated on the model will remain untouched.

You should probably use this method when only accepting new uploads.

```php
// in a controller

public function yourMethod(YourFormRequest $request)
{
    // retrieve model 

    $yourModel
        ->addFromMediaLibraryRequest($request->get('images'))
        ->toMediaCollection('images');

    flash()->success('Your model has been saved.')
    
    return back();
}
```

### `syncFromMediaLibraryRequest` 

You should use this method when you are using the `x-media-library-collection` Blade component (or equivalent Vue or React component).

Here is an example where we are going to sync that the contents of the `images` key in the request to the media library. 
In this example we use the `images` key, but of course you should use the name that you used.

All media associated with `$yourModel` whose `uuid` is not present in the `images` array of the request will be deleted.

```php
// in a controller

public function yourMethod(YourFormRequest $request)
{
    // retrieve model 

    $yourModel
        ->syncFromMediaLibraryRequest($request->images)
        ->toMediaCollection('images');

    flash()->success('Your model has been saved.')
    
    return back();
}
```

After this code has been executed, the media, whose `uuid` is present in the `images` array of request, will be in the `images collection of `$yourModel`.

```php
$yourModel->getMedia('images'); // the media that we just synced will be returned.
```

### Handling custom properties

If you are using properties for your media items you should pass the names of the custom properties you expect to the `withCustomProperties` method. Only these custom properties will be accepted.

```php
$yourModel
    ->syncFromMediaLibraryRequest($request->images)
    ->withCustomProperties('extra_field', 'another_extra_field')
    ->toMediaCollection('images');
```

## Customizing the preview images

All Blade, Vue and React components will display previews images that are generated by the `preview` conversion of
the `TemporaryUpload` model. This conversion will create a 500x500 representation of the image.

You can customize this by registering a preview manipulation. Typically, this would be done in a service provider.
Here's an example where we will create 300x300 previews

```php
use Spatie\MediaLibraryPro\Models\TemporaryUpload;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\Image\Manipulations;

// in a service provider
TemporaryUpload::manipulatePreview(function(Conversion $conversion) {
    $conversion->fit(Manipulations::FIT_CROP, 300, 300);
});
```

The components will use the `preview` conversion of models that have made associated to them. For example, if you have
a `$blogPost` model, and you use the components to display the media associated to that model, the components will
use `preview` conversion on the `BlogPost` model.

Make sure such an `preview` conversion exists for each model that handles media. We highly recommend to use `nonQueued`
so the image is immediately available.
