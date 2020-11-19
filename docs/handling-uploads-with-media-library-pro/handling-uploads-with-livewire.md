---
title: Handling uploads with Livewire
weight: 4
---

You can make use of the `x-media-library-attachment` and `x-media-library-collection` inside of the views of your own Livewire components.

## Getting started

Make sure to have followed [Livewire's installation instructions](https://laravel-livewire.com/docs/installation).

Make sure Alpine is available on the page as well. The easiest way is to include it from a CDN:

```html
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.6.0/dist/alpine.min.js" defer></script>
```

Visit [the Alpine repo](https://github.com/alpinejs/alpine) for more installation options.

## Handling a single upload

You can use `x-media-library-attachment` component to upload a single file.

![Screenshot of the attachment component](/docs/laravel-medialibrary/v9/images/pro/attachment.png)

Here's how that might look like in the view of your Livewire component

```html
<form method="POST" wire:submit.prevent="submit">
   
    <input id="name" wire:model.debounce.500ms="name">

    <x-media-library-attachment name="myUpload" />

    <button type="submit">Submit</button>
</form>
```

In your Livewire component you must:
- use the `Spatie\MediaLibraryPro\Http\Livewire\Concerns\WithMedia` trait
- add a public property `$mediaComponentNames` set to array that contains all the names of media library pro components that you are going to use. 
- for each component that you are going to use you should add a public property with the name you use in the view for that component (in the example above: `myUpload`)

Here is an example component:

```php
namespace App\Http\Livewire;

use App\Models\YourModel;
use Livewire\Component;
use Spatie\MediaLibraryPro\Http\Livewire\Concerns\WithMedia;

class MyForm extends Component
{
    use WithMedia;

    public $name;

    public $message = '';

    public $mediaComponentNames = ['myUpload'];

    public $myUpload;

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
        $this->clearMedia();
    }

    public function render()
    {
        return view('livewire.my-form');
    }
}
```

Immediately after a file has been uploaded it will be stored as a temporary upload.  In the method that handles the form submission you must use the `addFromMediaLibraryRequest` method to move the uploaded file to the model you want. 

To clear out an uploaded file from being displayed, you can call `clearMedia()`. This method will only clear the uploaded file from view, uploaded files will not be deleted.

If you are using multiple attachment components and only want to clear out specificy ones, pass the name of component to `clearMedia`.

```php
$this->clearMedia('myUpload')
```

### Validating a single upload

You can pass any Laravel validation rule to the rules prop of the `x-media-library-attachment` component. Here's an example where only `jpeg` and `pngs` will be accepted.

```html
<x-media-library-attachment name="myUpload" rules="mimes:jpeg,png"/>
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

Uploading multiple files is very similar to uploading a single file. The only thing you need to the `x-medialibrary-attachment` in the view is `multiple`.

```html
<form method="POST" wire:submit.prevent="submit">
   
    <input id="name" wire:model.debounce.500ms="name">

    <x-media-library-attachment multiple name="images" />

    <button type="submit">Submit</button>
</form>
```

![Screenshot of the attachment component](/docs/laravel-medialibrary/v9/images/pro/multiple.png)

In your Livewire component you must:
- use the `Spatie\MediaLibraryPro\Http\Livewire\Concerns\WithMedia` trait
- add a public property `$mediaComponentNames` set to array that contains all the names of media library pro components that you are going to use.
- for each component that you are going to use you should add a public property with the name you use in the view for that component (in the example above: `myUpload`)

Here is an example component:

```php
namespace App\Http\Livewire;

use App\Models\YourModel;
use Livewire\Component;
use Spatie\MediaLibraryPro\Http\Livewire\Concerns\WithMedia;

class MyForm extends Component
{
    use WithMedia;

    public $name;

    public $message = '';

    public $mediaComponentNames = ['images'];

    public $images;

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
        
        $this->clearMedia();
    }

    public function render()
    {
        return view('livewire.my-form');
    }
}
```

### Validating multiple uploads

You can pass any Laravel validation rule to the rules prop of the `x-media-library-attachment` component. Here's an example where only `jpeg` and `pngs` will be accepted.

```html
<x-media-library-attachment name="images" rules="mimes:jpeg,png"/>
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

## Administer the contents of a media library collection

You can manage the entire contents of a media library collection with `x-media-library-collection` component. This
component is intended to use in admin sections.

Here is an example where we are going to administer an `images` collection of a `$blogPost` model. We assume that you
already [prepared the model](/docs/laravel-medialibrary/v9/basic-usage/preparing-your-model) to handle uploads.

```html
<form method="POST" wire:submit.prevent="submit">

    <input id="name" wire:model.debounce.500ms="name">

    <x-media-library-collection
        name="images"
        :model="$blogPost"
        collection="images"
    />

    <button type="submit">Submit</button>
</form>
```

In your Livewire component you must:
- use the `Spatie\MediaLibraryPro\Http\Livewire\Concerns\WithMedia` trait
- add a public property `$mediaComponentNames` set to array that contains all the names of media library pro components that you are going to use.
- for each component that you are going to use you should add a public property with the name you use in the view for that component (in the example above: `myUpload`)

Here is an example component:

```php
namespace App\Http\Livewire;

use App\Models\BlogPost;
use Livewire\Component;
use Spatie\MediaLibraryPro\Http\Livewire\Concerns\WithMedia;

class MyForm extends Component
{
    use WithMedia;

    public $name;

    public $message = '';

    public $mediaComponentNames = ['images'];

    public $images;

    public function submit()
    {
        $formSubmission = BlogPost::create([
            'name' => $this->name,
        ]);

        $formSubmission
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

You can pass any Laravel validation rule to the rules prop of the `x-media-library-collection` component. Here's an example where only `jpeg` and `pngs` will be accepted.

```html
<x-media-library-collection name="images" rules="mimes:jpeg,png"/>
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

Media library supports [custom properties](/docs/laravel-medialibrary/v9/advanced-usage/using-custom-properties) to be saved on a media item. By
default, the  `x-media-library-collection` component doesn't show the custom properties. To add them you should create a
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

You should then pass the path to that view to the `fields-view` prop of the `x-media-library-collection` component.

```html
<x-media-library-collection
    name="images"
    :model="$formSubmission"
    collection="images"
    fields-view="app.your-custom-properties-blade-view-path"
/>
```

This is how that will look like.

![Screenshot of custom property](/docs/laravel-medialibrary/v9/images/pro/extra.png)

In your Livewire component, you can validate the custom properties like this. This example assumes that you have set the `name` attribute of `x-media-library-collection` to `images`.

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

To get started with upload files to `s3`, make sure to follow Livewire's instructions on [how to upload directly to S3](https://laravel-livewire.com/docs/2.x/file-uploads#upload-to-s3).

Next, make sure you configured the media disk that uses the S3 driver.

With that configuration in place, the `attachment` component will now upload directly to S3.
