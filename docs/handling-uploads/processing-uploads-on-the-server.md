---
title: Processing uploads on the server
weight: 5
---

All Blade, Vue and React components communicate with the server in the same way. 

When a form that uses the upload components is submitted, the files have already been uploaded to the server. The components send the UUID of a `Media` model. For new files, that model is associated with a `TemporaryUpload` model. Files that were already validated and processed in a previous request, are associated with another Eloquent model.

Every component will pass data in a key of a request. The name of that key is the name you passed to the `name` prop of any of the components.

```html 
// data will get passed via the `avatar` key of the request.

<x-medialibrary-attachment name="avatar" />
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

If you're [using custom properties](TODO add link to custom properties on handling blade page), you can validate them with the `customProperty` function. The first argument should be the name of the custom property you are validating. The second argument should be a string or an array with rules you'd like to use.

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

After you've validated the response, you should persist the changes to the media library. The media library provides two methods for that: `syncFromMediaLibraryRequest` and `addFromMediaLibraryRequest`. Both these methods are available on all [models that handle media](TODO: add link).

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

You should use this method when you are using the `x-medialibrary-collection` Blade component (or equivalent Vue or React component).

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


