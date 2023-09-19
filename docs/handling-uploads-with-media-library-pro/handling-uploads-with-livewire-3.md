---
title: Handling uploads with Livewire 3
weight: 5
---

Media Library Pro v3 is compatible with Livewire v3.

You can make use of the `livewire:media-library` Livewire component inside of the views of your own Livewire components.

## Getting started

Make sure to have followed [Livewire's installation instructions](https://livewire.laravel.com).

You must add `@mediaLibraryStyles` before the closing `</head>` tag of your layout file.

## Demo application

In [this repo on GitHub](https://github.com/spatie/laravel-medialibrary-pro-app), you'll find a demo Laravel application in which you'll find examples of how to use Media Library Pro inside your Livewire components.

If you are having trouble using the components, take a look in that app to see how we've done it.

## Handling a single upload

You can use `livewire:media-library` component to upload a single file.

![Screenshot of the attachment component](/docs/laravel-medialibrary/v10/images/pro/attachment.png)

Here's how that might look like in the view of your Livewire component:

```html
<form method="POST" wire:submit.prevent="submit">
   
    <input id="name" wire:model.debounce.500ms="name">

    <livewire:media-library wire:model="myUpload" />

    <button type="submit">Submit</button>
</form>
```

In your Livewire component you must:
- use the `Spatie\MediaLibraryPro\Livewire\Concerns\WithMedia` trait
- add a public property that defaults to an empty array for binding the media library component to (in the example above: `myUpload`, of course you can use any name you want)
- for each component that you are going to use you should add a public property with the name you use in the view for that component (in the example above: `myUpload`)

Here is an example component:

```php
namespace App\Http\Livewire;

use App\Models\YourModel;
use Livewire\Component;
use Spatie\MediaLibraryPro\Livewire\Concerns\WithMedia;

class MyForm extends Component
{
    use WithMedia;

    public $name;

    public $message = '';

    public $myUpload = [];

    public function submit()
    {
        $formSubmission = YourModel::create([
            'name' => $this->name,
        ]);

        $formSubmission
            ->addFromMediaLibraryRequest($this->myUpload)
            ->toMediaCollection('images');

        $this->message = 'Your form has been submitted';

        $this->name = '';
        $this->myUpload = null;
    }

    public function render()
    {
        return view('livewire.my-form');
    }
}
```

Immediately after a file has been uploaded it will be stored as a temporary upload.  In the method that handles the form submission you must use the `addFromMediaLibraryRequest` method to move the uploaded file to the model you want. 

To clear out an uploaded file from being displayed, you can set bound property `myUpload` to `null`. This will only clear the uploaded file from view, uploaded files will not be deleted.

### Validating a single upload

You can pass any Laravel validation rule to the rules prop of the `livewire:media-library` component. Here's an example where only `jpeg` and `png` will be accepted.

```html
<livewire:media-library name="myUpload" rules="mimes:jpeg,png"/>
```

You can make the upload required by validating it in your Livewire component:

```php
// in the method that handles the form submission inside your livewire component

public function submit()
{
    $this->validate([
        'myUpload' => 'required',
    ]);
    
    // process the form submission
}
```

## Handling multiple uploads

Uploading multiple files is very similar to uploading a single file. The only thing you need to add is a `multiple` property

```html
<form method="POST" wire:submit.prevent="submit">
   
    <input id="name" wire:model.debounce.500ms="name">

    <livewire:media-library wire:model="images" multiple />

    <button type="submit">Submit</button>
</form>
```

![Screenshot of the attachment component](/docs/laravel-medialibrary/v10/images/pro/multiple.png)

In your Livewire component you must:
- use the `Spatie\MediaLibraryPro\Livewire\Concerns\WithMedia` trait
- add a public property `$images` that we can bind the uploads to


Here is an example component:

```php
namespace App\Http\Livewire;

use App\Models\YourModel;
use Livewire\Component;
use Spatie\MediaLibraryPro\Livewire\Concerns\WithMedia;

class MyForm extends Component
{
    use WithMedia;

    public $name;

    public $message = '';

    public $images = [];

    public function submit()
    {
        $formSubmission = YourModel::create([
            'name' => $this->name,
        ]);

        $formSubmission
            ->addFromMediaLibraryRequest($this->images)
            ->toMediaCollection('images');

        $this->message = 'Your form has been submitted';

        $this->name = '';
        
        $this->images = [];
    }

    public function render()
    {
        return view('livewire.my-form');
    }
}
```

### Validating multiple uploads

You can pass any Laravel validation rule to the rules prop of the `livewire:media-library` component. Here's an example where only `jpeg` and `png` will be accepted.

```html
<livewire:media-library wire:model="images" rules="mimes:jpeg,png"/>
```

You can make the upload required by validating it in your Livewire component. Here's an example where at least one upload is required, but more than three uploads are not allowed.

```php
// in the method that handles the form submission inside your Livewire component

public function submit()
{
    $this->validate([
        'images' => 'required|max:3',
    ]);
    
    // process the form submission
}
```

## Administer the contents of a media library collection

You can manage the entire contents of a media library collection with `livewire:media-library` component. This
component is intended for use in admin sections.

Here is an example where we are going to administer an `images` collection of a `$blogPost` model. We assume that you
already [prepared the model](/docs/laravel-medialibrary/v10/basic-usage/preparing-your-model) to handle uploads.

```html
<form method="POST" wire:submit.prevent="submit">

    <input id="name" wire:model.debounce.500ms="name">

    <livewire:media-library
        collection="images"
        :model="$blogPost"
        wire:model="images"
    />

    <button type="submit">Submit</button>
</form>
```

In your Livewire component you must:
- use the `Spatie\MediaLibraryPro\Livewire\Concerns\WithMedia` trait
- add a public property `$images` that we can bind to upload to (you can use any name you want)
- pass the Eloquent model that the collection is saved on to the component, in this case `$blogPost`

Here is an example component:

```php
namespace App\Http\Livewire;

use App\Models\BlogPost;
use Livewire\Component;
use Spatie\MediaLibraryPro\Livewire\Concerns\WithMedia;

class MyForm extends Component
{
    use WithMedia;

    public $name;

    public $message = '';

    public $images;

    public $blogPost;

    public function mount()
    {
        $this->blogPost = BlogPost::first();
    }

    public function submit()
    {
        $this->blogPost->update(['name' => $this->name]);

        $this->blogPost
            ->addFromMediaLibraryRequest($this->images)
            ->toMediaCollection('images');

        $this->message = 'Your form has been submitted';       
    }

    public function render()
    {
        return view('livewire.my-form');
    }
}
```

### Validating the collection

You can pass any Laravel validation rule to the rules prop of the `livewire:media-library` component. Here's an example where only `jpeg` and `png` will be accepted.

```html
<livewire:media-library wire:model="images" collection="images" :model="$blogPost" rules="mimes:jpeg,png"/>
```

You can make the upload required by validating it in your Livewire component. Here's an example where at least one upload is required, but more than three uploads are not allowed.

```php
// in the method that handles the form submission inside your livewire component

public function submit()
{
    $this->validate([
        'images' => 'required|max:3',
    ]);
    
    // process the form submission
}
```

### Using custom properties

Media library supports [custom properties](/docs/laravel-medialibrary/v10/advanced-usage/using-custom-properties) to be saved on a media item. By
default, the  `livewire:media-library` component doesn't show the custom properties. To add them you should create a
blade view that will be used to display all form elements on a row in the component.

In this example we're going to add a custom property form field called `extra_field`.

```html
@include('media-library::livewire.partials.collection.fields')

<div class="media-library-field">
    <label class="media-library-label">Extra field</label>
    <input
        class="media-library-input"
        type="text"
        {{ $mediaItem->livewireCustomPropertyAttributes('extra_field') }}
    />

    @error($mediaItem->customPropertyErrorName('extra_field'))
    <span class="media-library-text-error">
       {{ $message }}
    </span>
    @enderror
</div>
```

You should then pass the path to that view to the `fields-view` prop of the `livewire:media-library` component.

```html
<livewire:media-library
    wire:model="images"
    :model="$formSubmission"
    collection="images"
    fields-view="app.your-custom-properties-blade-view-path"
/>
```

This is how that will look like.

![Screenshot of custom property](/docs/laravel-medialibrary/v10/images/pro/extra.png)

In your Livewire component, you can validate the custom properties like this. This example assumes that you have set the `name` attribute of `livewire:media-library` to `images`.

```php
// inside the method in your Livewire component that handles the form submission
public function submit()
{
    $this->validate([
        'images.*.custom_properties.extra_field' => 'required',
    ], ['required' => 'This field is required']);

    // process the form submission
}
```

## Uploading directly to S3

Currently, Livewire does not support uploading multiple files to S3. That's why only the `attachment` component can be used to upload files to S3.

To get started with uploading files to `s3`, make sure to follow Livewire's instructions on [how to upload directly to S3](https://livewire.laravel.com/docs/uploads#storing-uploaded-files).

Next, make sure you have configured the media disk that uses the S3 driver.

With that configuration in place, the `livewire:media-library` component will now upload directly to S3.
