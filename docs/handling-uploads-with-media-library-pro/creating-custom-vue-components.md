---
title: Creating custom Vue components
weight: 7
---

Both the Vue and UI components are built on top of the same core, with a language-specific abstraction layer in between that exposes some helper functions. You can extend the core that the UI components are built on. This allow you to customize the UI. This page will go into detail about these abstraction layers.

The Vue implementation uses a renderless component that exposes all the functions and values through a slot scope.

For more extensive examples, [see the pre-built UI components on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js)

## Getting started

```html
<template>
    <media-library-renderless
        ref="mediaLibrary"
        :initial-media="initialValue"
        :route-prefix="routePrefix"
        :validation-errors="validationErrors"
        :validation-rules="validationRules"
        :before-upload="beforeUpload"
        :after-upload="afterUpload"
        :multiple="multiple"
        @change="$emit('change', $event)"
        @is-ready-to-submit-change="$emit('is-ready-to-submit-change', $event)"
        @has-uploads-in-progress-change="$emit('has-uploads-in-progress-change', $event)"
    >
        <div
            slot-scope="{
                state,
                getImgProps,
                getDropZoneProps,
                getDropZoneListeners,
                getFileInputListeners,
                removeMedia,
                replaceMedia,
                getErrors,
                clearObjectErrors,
                clearInvalidMedia,
            }"
        >
            <input type="file" multiple v-on="getFileInputListeners()" />

            <img v-for="object in state.media" :key="object.attributes.uuid" v-bind="getImgProps(object)" />
        </div>
</template>

<script>
    import { MediaLibraryRenderless } from 'media-library-pro-vue2';

    export default{
        components: { MediaLibraryRenderless },
    }
</script>
```

In Vue 3, you would use the new `v-slot` attribute instead of `slot-scope`:

```html
v-slot="{ state, getImgProps, … }"
```

You can find a full list of available props for the renderless component [at the bottom of this page](TODO-link).

## Examples

For extensive examples you can have a look at the source of the pre-built UI components:

-   [Vue 2 attachment component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-vue2-attachment)
-   [Vue 2 collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-vue2-collection)
-   [Vue 3 attachment component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-vue3-attachment)
-   [Vue 3 collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-vue3-collection)

## Helper components

When building your own UI component using the Media Library Pro, you can also make use of these helper components. These are the same components that are used in the UI components.

### DropZone

See code on GitHub: [Vue 2](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue2/src/DropZone.vue) / [Vue 3](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue3/src/DropZone.vue)

Renderless component that exposes some props needed to render a file dropzone. Has a scoped slot that exposes `hasDragObject`, `isDropTarget` and `isValid`.

Emits `@clicked` and `@dropped`.

**props**

```js
props: {
    validationAccept: { default: () => [], type: Array },
},
```

### HiddenFields

See code on GitHub: [Vue 2](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue2/src/HiddenFields.vue) / [Vue 3](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue3/src/HiddenFields.vue)

Component that renders hidden input fields with the values of the entire MediaLibrary instance's media state. Only needed if you're planning on submitting forms traditionally (not with AJAX).

**props**

```js
props: {
    name: { required: true, type: String },
    mediaState: { default: () => [], type: Array },
},
```

### ItemErrors

See code on GitHub: [Vue 2](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue2/src/ItemErrors.vue) / [Vue 3](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue3/src/ItemErrors.vue)

Component that renders the errors for one media object.

Emits `@back`.

**props**

```js
props: {
    objectErrors: { required: true, type: Array },
},
```

### ListErrors

See code on GitHub: [Vue 2](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue2/src/listErrors/ListErrors.vue) / [Vue 3](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue3/src/listErrors/ListErrors.vue)

Component that can render the MediaLibrary instance's invalid media (`mediaLibrary.state.invalidMedia`).

Emits `@cleared`.

**props**

```js
props: {
    invalidMedia: { default: () => ({}), type: Array },
    topLevelErrors: { default: () => [], type: Array },
},
```

### Thumb

See code on GitHub: [Vue 2](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue2/src/Thumb.vue) / [Vue 3](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue3/src/Thumb.vue)

Component to display a file's thumbnail. If no preview for the file exists, it will attempt to display its extension. Also implements the [Uploader](#uploader) component to replace files.

Emits `@replaced`.

**props**

```js
props: {
    uploadInfo: { required: true, type: Object },
    validationRules: { required: false, type: Object },
    imgProps: { required: true, type: Object },
},
```

### Uploader

See code on GitHub: [Vue 2](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue2/src/Uploader.vue) / [Vue 3](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue3/src/Uploader.vue)

Component used to upload new media objects, or to replace an existing object's file. Is used by the [Thumb](#thumb) component.

Emits `@changed` and `@dropped`.

**props**

```js
props: {
    add: { default: true, type: Boolean },
    uploadInfo: { required: false, type: Object },
    multiple: { default: false, type: Boolean },
    validationRules: { required: false, type: Object },
    maxItems: { required: false, type: Number },
},
```

### Icons

See code on GitHub: [Vue 2](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue2/src/components/Icons.vue) / [Vue 3](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue3/src/components/Icons.vue)

Component that sets svg values for the packaged icons, required if you're planning on using the [icon](#icon) component.

### Icon

See code on GitHub: [Vue 2](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue2/src/components/Icon.vue) / [Vue 3](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue3/src/components/Icon.vue)

Renders an icon. Requires [icons](#icons) to be rendered on the page.

**Props**

```js
props: {
    icon: { required: true, type: String },
},
```

### IconButton

See code on GitHub: [Vue 2](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue2/src/components/IconButton.vue) / [Vue 3](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue3/src/components/IconButton.vue)

Renders a button with an icon. Requires [icons](#icons) to be rendered on the page.

**Props**

```js
props: {
    icon: { required: true, type: String },
},
```

**Parameters**

```js
handleClass?: string
```

## Props

| prop name                       | Default value                | Description                                                                                                                                                             |
| ------------------------------- | ---------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| name                            |                              |                                                                                                                                                                         |
| initial-value                   | `[]`                         |                                                                                                                                                                         |
| route-prefix                    | `"media-library-pro"`        |                                                                                                                                                                         |
| validation-rules                |                              | Refer to [validation](../handling-uploads-with#validation-rules) section                                                                                                |
| validation-errors               |                              | The standard Laravel validation error object                                                                                                                            |
| max-items                       |                              |                                                                                                                                                                         |
| max-size-for-preview-in-bytes   |                              |                                                                                                                                                                         |
| vapor                           | `false`                      | Set to true if you will deploy your application to Vapor, this enables uploading of the files to S3. [Read more](../handling-uploads-with-vue#using-with-laravel-vapor) |
| vapor-signed-storage-url        | `"vapor/signed-storage-url"` |                                                                                                                                                                         |
| multiple                        | `true`                       |                                                                                                                                                                         |
| before-upload                   |                              | A method that is run right before a temporary upload is started. You can throw an `Error` from this function with a custom validation message                           |
| after-upload                    |                              | A method that is run right after a temporary upload has completed, `{ success: true, uuid }`                                                                            |
| @change                         |                              |                                                                                                                                                                         |
| @is-ready-to-submit-change      |                              | Emits a boolean that tells whether the MediaLibrary instance is ready to submit (has no uploads in progress and has no frontend validation errors)                      |
| @has-uploads-in-progress-change |                              | Emits a boolean that tells whether the MediaLibrary instance currently has uploads in progress                                                                          |

## Returned parameters in scoped slot

| variable name                   | Description                                                                                                                                                                                                                                             |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| mediaLibrary                    | Ref to the MediaLibrary instance                                                                                                                                                                                                                        |
| state                           | The state of the MediaLibrary instance. Includes `media` (an array of currently added files), `invalidMedia` (files that encountered a frontned validation error) and `validationErrors` (backend validation errors mapped to uuids)                    |
| isReadyToSubmit                 | Boolean that tells whether the MediaLibrary instance is ready to submit (has no uploads in progress and has no frontend validation errors)                                                                                                              |
| hasUploadsInProgress            | Boolean that tells whether the MediaLibrary instance currently has uploads in progress                                                                                                                                                                  |
| getImgProps                     | Method that expects a media object, and returns an object with props needed for an `img` tag                                                                                                                                                            |
| getNameInputProps               | Method that expects a media object, and returns an object with props needed for an `input type=text` tag that controls an object's name attribute                                                                                                       |
| getNameInputListeners           | Method that expects a media object, and returns an object with listeners needed for an `input type=text` tag that controls an object's name attribute                                                                                                   |
| getNameInputErrors              | Method that expects a media object, and returns an array of backend validation errors for the `name` attribute                                                                                                                                          |
| getCustomPropertyInputProps     | Method that expects a media object and a custom property name, and returns an object with props needed for an `input` tag that controls that custom property                                                                                            |
| getCustomPropertyInputListeners | Method that expects a media object and a custom property name, and returns an object with listeners needed for an `input` tag that controls that custom property                                                                                        |
| getCustomPropertyInputErrors    | Method that expects a media object and a custom property name, and returns an array of backend validation errors for that custom property                                                                                                               |
| getFileInputProps               | Method that expects a media object, and returns an object with props needed for an `input type=file` tag that controls an object's name attribute                                                                                                       |
| getFileInputListeners           | Method that expects a media object, and returns an object with listeners needed for an `input type=file` tag that controls an object's name attribute                                                                                                   |
| getDropZoneProps                | Method that expects a media object, and returns an object with props needed for a file dropzone (`validationRules` and `maxItems`)                                                                                                                      |
| getDropZoneListeners            | Method that expects a media object, and returns an object with listeners needed for a file dropzone (`@drop`)                                                                                                                                           |
| addFile                         | Method that allows a user to pass in an array of files to be added to the MediaLibrary instance's media state                                                                                                                                           |
| addFile                         | Method that allows a user to pass in a File to be added to the MediaLibrary instance's media state                                                                                                                                                      |
| removeMedia                     | Method that expects a media object, and removes it from the MediaLibrary instance's media state                                                                                                                                                         |
| setOrder                        | Method that expects an array of uuids, and sets the `order` attribute of the media objects in the media state                                                                                                                                           |
| setProperty                     | Method that expects a media object, a key (`client_preview`, `attributes.preview_url`, `attributes.size`, `attributes.extension` …), and a value, and sets this property on the media object. This method usually shouldn't be used outside of the core |
| setCustomProperty               | Method that expects a media object, a custom property key (`tags`, `caption`, `alt` …), and a value, and sets this property on the media object                                                                                                         |
| replaceMedia                    | Method that expects a media object and a File, and uploads this file and replaces it on the media object                                                                                                                                                |
| getErrors                       | Method that expects a media object, and returns an array of errors that it may have encountered                                                                                                                                                         |
| clearObjectErrors               | Method that expects a media object, and clears its errors                                                                                                                                                                                               |
| clearInvalidMedia               | Method that clears the MediaLibrary instance's invalidMedia state                                                                                                                                                                                       |
