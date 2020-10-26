---
title: Handling uploads with Vue
weight: 5
---

Media Library Pro provides beautiful UI components for Vue. They pack a lot of features: temporary uploads, custom property inputs, frontend validation, and robust error handling.

The `MediaLibraryAttachment` component can upload one or more files with little or no extra information. The attachment component is a lightweight solution for small bits of UI like avatar fields.

![Screenshot of the MediaLibraryAttachment Vue component](/docs/laravel-medialibrary/v9/images/pro/attachment.png)

The `MediaLibraryCollection` component can upload multiple files with custom properties. The collection component shines when you need to manage media, like in backoffices.

![Screenshot of the MediaLibraryCollection Vue component](/docs/laravel-medialibrary/v9/images/pro/collection.png)

If neither of these fit the bill, we've exposed a set of APIs for you to be bold and [roll your own components](#).

## Basic setup

First, the server needs to be able to catch your incoming uploads. Register the Media Library `UploadController` in your routes file.

```php
// routes/web.php

use Spatie\MediaLibraryPro\Http\Controllers\UploadController;

Route::post('media-library-upload-components', UploadController::class);
```

The Vue components post data to `/media-library-upload-components` by default. If you registered the controller on a different URL, pass it to the `upload-endpoint` prop of your Vue components.

```html
<media-library-attachment name="avatar" upload-endpoint="temp-upload" />
```

The components aren't available through npm, but are located in `vendor/spatie/laravel-medialibrary-pro/ui` when you install the package through composer. This makes for very long import statements, which you can clean up by adding some configuration to your Webpack/Laravel Mix configuration:

**laravel-mix >6**

```js
// webpack.mix.js

mix.override((webpackConfig) => {
    webpackConfig.resolve.modules = [
        "node_modules",
        __dirname + "/vendor/spatie/laravel-medialibrary-pro/ui",
    ];
}
```

**laravel-mix <6**

```js
// webpack.mix.js

mix.webpackConfig({
    resolve: {
        modules: [
            "node_modules",
            __dirname + "/vendor/spatie/laravel-medialibrary-pro/ui",
        ],
    },
});
```

This will force Webpack to look in `vendor/spatie/laravel-medialibrary-pro/ui` when resolving imports, and allows you to shorten your import to this:

```js
import MediaLibraryAttachment from "medialibrary-pro-vue-attachment";
```

If you're using TypeScript, you will also have have to add this to your tsconfig:

```json
// tsconfig.json

{
    "compilerOptions": {
        "paths": {
            "*": ["vendor/spatie/laravel-medialibrary-pro/ui/*"]
        }
    }
}
```

To use a component in your Blade templates, import the components you plan to use in your `app.js` file, and add them to your main Vue app's `components` object.

```js
import Vue from "vue";
import MediaLibraryAttachment from "medialibrary-pro-vue-attachment";
import MediaLibraryCollection from "medialibrary-pro-vue-collection";

const app = new Vue({
    el: "#app",

    components: {
        MediaLibraryAttachment,
        MediaLibraryCollection,
    },
});
```

You can now use them in any `.blade.php` file in your application.

```html
<!-- posts/edit.blade.php -->

<div id="app">
    <form>
        <media-library-attachment name="cover" />
        <media-library-collection name="images" />
        <button>Submit</button>
    </form>
</div>
```

You may also choose to import the components on the fly in a `.vue` file.

```html
<!-- EditPost.vue -->

<template>
    <form>
        <media-library-attachment name="cover" />
        <media-library-collection name="images" />
        <button>Submit</button>
    </form>
</template>

<script>
    import MediaLibraryAttachment from "medialibrary-pro-vue-attachment";
    import MediaLibraryCollection from "medialibrary-pro-vue-collection";

    export default {
        components: {
            MediaLibraryAttachment,
            MediaLibraryCollection,
        },
    };
</script>
```

## Your first components

The most basic components have a `name` prop. This name will be used to identify the media when it's uploaded to the server.

```html
<!-- MyImageUploader.vue -->

<template>
    <form>
        <media-library-attachment name="avatar" />
        <media-library-collection name="downloads" />
        <button>Submit</button>
    </form>
</template>

<script>
    import MediaLibraryAttachment from "medialibrary-pro-vue-attachment";
    import MediaLibraryCollection from "medialibrary-pro-vue-collection";

    export default {
        components: {
            MediaLibraryAttachment,
            MediaLibraryCollection,
        },
    };
</script>
```

If your form modifies an existing set of media, you may pass it through in the `initial-value` prop.

You can retrieve your initial values in Laravel using `$yourModel->getMedia($collectionName);`, this will also take care of any `old` values after an invalid form submit. You can also use this straight in your blade file:

```html
<form>
    <media-library-attachment
        name="avatar"
        :initial-value="@json($post->getMedia('avatar'))"
    />

    <media-library-collection
        name="downloads"
        :initial-value="@json($post->getMedia('downloads'))"
    />

    <button>Submit</button>
</form>
```

Under the hood, these components create hidden `<input />` fields to keep track of the form values on submit. If you would like to submit your values asynchronously, refer to the `Asynchronously submit data` section.

You'll probably want to validate what gets uploaded. Use the `validation-rules` prop, and don't forget to pass Laravel's validation errors too. The validation errors returned from the server will find errors under the key used in your `name` prop.

```html
<form>
    <media-library-attachment
        name="avatar"
        :initial-value="@json($post->getMedia('avatar'))"
        :validation-rules="{ accept: ['image/png', 'image/jpeg'], maxSizeInKB: 5000 }"
        :validation-errors="@json($errors)"
    />

    <media-library-collection
        name="downloads"
        :initial-value="@json($post->getMedia('downloads'))"
        :validation-rules="{ accept: ['image/png', 'image/jpeg'], maxSizeInKB: 5000 }"
        :validation-errors="@json($errors)"
    />

    <button>Submit</button>
</form>
```

You can also set the maximum amount of images that users can be uploaded using the `max-items` prop. Don't forget to set the `multiple` prop for the attachment component.

```html
<form>
    <media-library-attachment name="files" :max-items="2" multiple />

    <media-library-collection name="downloads" :max-items="5" />

    <button>Submit</button>
</form>
```

See the [Validation rules section](#validation-rules) for a complete list of all possible validation rules.

### Using custom properties

The Media Library supports [custom properties](/docs/laravel-medialibrary/v9/advanced-usage/using-custom-properties) to be saved on a media item. The values for these can be chosen by your users. By default, the `MediaLibraryAttachment` component doesn't show any input fields, and the `MediaLibraryCollection` component only shows a `name` field, with the option to add more fields.

Use the `fieldsView` scoped slot to add some fields:

```html
<media-library-collection name="images" :initial-value="{{ $images }}">
    <template
        slot="fieldsView"
        slot-scope="{
            getCustomPropertyInputProps,
            getCustomPropertyInputListeners,
            getCustomPropertyInputErrors,
            getNameInputProps,
            getNameInputListeners,
            getNameInputErrors,
        }"
    >
        <div class="medialibrary-properties">
            <div class="medialibrary-field">
                <label class="medialibrary-label">Name</label>
                <input
                    class="medialibrary-input"
                    v-bind="getNameInputProps()"
                    v-on="getNameInputListeners()"
                    dusk="medialibrary-field-name"
                />
                <p
                    v-for="error in getNameInputErrors()"
                    :key="error"
                    class="medialibrary-text-error"
                >
                    @{{ error }}
                </p>
            </div>

            <div class="medialibrary-field">
                <label class="medialibrary-label">Extra field</label>
                <input
                    class="medialibrary-input"
                    v-bind="getCustomPropertyInputProps('extra_field')"
                    v-on="getCustomPropertyInputListeners('extra_field')"
                    dusk="medialibrary-extra-field"
                />
                <p
                    v-for="error in getCustomPropertyInputErrors('extra_field')"
                    :key="error"
                    class="medialibrary-text-error"
                >
                    @{{ error }}
                </p>
            </div>
        </div>
    </template>
</media-library-collection>
```

When you add an image to your collection, it will look like this.

![Screenshot of custom property](/docs/laravel-medialibrary/v9/images/pro/extra.png)

### Customizing the file properties

When uploading a file, some properties appear by default: its extension, filesize and a remove or download button (respectively for the attachment or component component).

You can customize what is displayed here by using the `propertiesView` scoped slot:

```html
<media-library-attachment
    name="images"
    :initial-value="{{ $images }}"
>
    <template slot="propertiesView" slot-scope="{ object }">
        <div class="medialibrary-property">
            {{ object.attributes.name }}
        </div>
    </template>
</media-library-collection>
```

See the [Props section](#props) for a complete list of all props.

### Asynchronously submit data

If you don't want to use traditional form submits to send your data to the backend, you will have to keep track of the current value of the component using the `onChange` handler. The syntax is the same for all UI components:

```html
<template>
    <div>
        <media-library-attachment
            …
            :validation-errors="validationErrors"
            @change="onChange"
        />

        <media-library-collection
            …
            :validation-errors="validationErrors"
            @change="onChange"
        />

        <button @click="submitForm">Submit</button>
    </div>
</template>

<script>
    import Axios from "axios";

    export default {
        props: { values },

        data() {
            return {
                validationErrors: {},
                media: this.values.media,
            };
        },

        methods: {
            onChange(media) {
                this.media = media;
            },

            submitForm() {
                Axios.post("endpoint", { media: this.media }).catch(
                    (error) => (this.validationErrors = error.data.errors)
                );
            },
        },
    };
</script>
```

### Checking the upload state

The components keep track of whether they're ready to be submitted, you can use this to disable a submit button while a file is still uploading or when there are frontend validation errors. This value can be tracked by listening to a `is-ready-to-submit-change` event on the components:

```html
<template>
    <form>
        <media-library-attachment
            name="avatar"
            @is-ready-to-submit-change="isReadyToSubmit = $event"
        />

        <button :disabled="isReadyToSubmit">Submit</button>
    </form>
</template>

<script>
    import MediaLibraryAttachment from "medialibrary-pro-vue-attachment";

    export default {
        components: { MediaLibraryAttachment },

        data() {
            return {
                isReadyToSubmit: true,
            };
        },
    };
</script>
```

### Validation rules

There are a couple of different props that could be labeled as validation. We've got `validationRules`, `maxItems` and `beforeUpload`.

**validationRules**

In the `validationRules` object, we've got the `accept` property, which expects an array of mimetypes as strings.

The `minSizeInKB` and `maxSizeInKB` properties set the minimum and maximum size of any individual file.

```html
<media-library-attachment
    name="avatar"
    :validation-rules="{
        accept: ['image/jpeg', 'image/gif', 'application/pdf'],
        minSizeInKB: 512,
        maxSizeInKB: 512,
    }"
/>
```

**maxItems**

Set the maximum amount of items in the collection/attachment component at any time.

```html
<media-library-attachment name="avatar" :max-items="3" />
```

**beforeUpload**

Pass a method to `before-upload` that accepts a [file](https://developer.mozilla.org/en-US/docs/Web/API/File) parameter. Return any value (or resolve a Promise with any value) from this function to upload the file. Throw an Error in this function to cause the file not to be uploaded, and display your error message.

```html
<template>
    <media-library-attachment
        name="avatar"
        :before-upload="checkFileValidity"
    />
</template>

<script>
    export default {
        …

        methods: {
            checkFileValidity(file) {
                return new Promise((resolve) => {
                    if (file.size < 1000) {
                        return resolve();
                    }

                    throw new Error("The uploaded file is too big");
                });
            }
        },
    }
</script>
```

## Props

| prop name                     | Default value                                         | Description                                                                                                                                                                       |
| ----------------------------- | ----------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| name                          |                                                       |                                                                                                                                                                                   |
| initial-value                 | `[]`                                                  |                                                                                                                                                                                   |
| upload-endpoint               | `"media-library-upload-components"`                   |                                                                                                                                                                                   |
| validation-rules              |                                                       | Refer to [validation rules](#validation-rules) section                                                                                                                            |
| validation-errors             |                                                       | The standard Laravel validation error object                                                                                                                                      |
| multiple                      | `false` (always `true` in the `collection` component) | Only exists on the `attachment` components                                                                                                                                        |
| max-items                     | `1` when `multiple` = `false`, otherwise `undefined   |                                                                                                                                                                                   |
| max-size-for-preview-in-bytes | `5242880` (5 MB)                                      | When an image is added, the component will try to generate a local preview for it. This is done on the main thread, and can freeze the component and/or page for very large files |
| sortable                      | `true`                                                | Only exists on the `collection` components. Allows the user to drag images to change their order, this will be reflected by a zero-based `order` attribute in the value           |
| ref                           |                                                       | Used to set a reference to the mediaLibrary instance, so you can change the internal state of the component.                                                                      |
| before-upload                 |                                                       | A method that is run right before a temporary upload is started. You can throw an `Error` from this function with a custom validation message                                     |
| after-upload                  |                                                       | A method that is run right after a temporary upload has completed, `{ success: true, uuid }`                                                                                      |
| @change                       |                                                       |                                                                                                                                                                                   |
| @is-ready-to-submit-change    |                                                       | Refer to [Checking the upload state](#checking-the-upload-state) section                                                                                                          |
