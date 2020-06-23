---
title: Processing uploads on the server
weight: 5
---

All Blade, Vue and React components communicate with the server in the same way. 

When a form that uses the upload components is submitted, the files have already been uploaded to the server. The components send the UUID of a `Media` model that is either associated with a `TemporaryUpload` model (for new files) or another Eloquent model (for files that were already processed and associated with a model previously)

Every component will pass data in a key of a request. The name of that key is the name you passed to the `name` prop of any of the components.

```html 
// data will get pass via the `avatar` key of the request.

<x-media-library-upload name="avatar" />
```

The content of that request key will be an array. For each file uploaded that array will hold an array with these keys.

- `name`: the name of the uploaded file
- `uuid`: the UUID of a `Media` model. For newly uploaded files that have not been associated to a model yet, the `Media` model will be associated with a `TemporaryUpload` model
- `order`: the order in which this item should be stored in a media collection.

## Validating responses

Even if you validated individual uploads, we highly recommend always validating responses sent by the components on the server as well.

Typically, you would handle validation in a form request. Since all components respond with an array you can use Laravel's default way of validating arrays. In this example we assume that a component was configured to use the `images` key of the request. We validate that there was at least one item uploaded, but no more than 5. All images should have a name.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MyRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required',
            'images' => ['min:1', 'max:5'],
            'images.*.name' => 'required',
        ];
    }
}
```

In addition to Laravel's default validation rules, the media library provides a couple extra ones as well. All the rules are available via the `Spatie\MediaLibraryPro\Rules\UploadedMedia` class. Here's an example where we make sure that the total size of all files is no higher than 5 MB. Additionally each file should at least be 20 KB.

```php
namespace App\Http\Requests;

use Spatie\MediaLibraryPro\Rules\UploadedMedia;
use Illuminate\Foundation\Http\FormRequest;

class MyRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required',
            'images' => ['min:1', 'max:5', UploadedMedia::maxTotalSizeInKb(5 * 1024)],
            'media.*' => [
                UploadedMedia::minzeInKb(20),
            ],
            'images.*.name' => 'required',
        ];
    }
}
```

## Processing responses

TO DO:
