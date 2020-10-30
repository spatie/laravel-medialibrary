---
title: Creating custom Vue components
weight: 7
---

Both the Vue and React UI components are built on top of the same core, with a language-specific abstraction layer in between that exposes some helper functions. You can extend the core that the UI components are built on. This allow you to customize the UI. This page will go into detail about these abstraction layers.

The standard Medialibary UI components are built using helper components. These helper components can be used individually to make custom components. You can read more about the helper components in the [Helper Components](TODO-link) section.

## Vue

The Vue implementation uses a renderless component that exposes all the functions and values through a slot scope.

Note: in a future version if we decide to refactor the Vue implementation to work with the new composition API that was released in Vue 3.

For more extensive examples, [see the pre-built UI components on GitHub](TODO-link:#examples)

### Getting started

```html
<template>
    <media-library-renderless
        ref="mediaLibrary"
        :initial-media="initialValue"
        :upload-endpoint="uploadEndpoint"
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
            <input type="file" :multiple="true" v-on="getFileInputListeners()" />
        </div>
</template>

<script>
    import { MediaLibraryRenderless } from 'media-library-pro-vue';

    export default{
        components: { MediaLibraryRenderless },
    }
</script>
```

You can find a full list of props [at the bottom of this page](TODO-link).

## Examples

For extensive examples you can have a look at the source of the pre-built UI components:

-   [Vue attachment component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-vue-attachment)
-   [Vue collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-vue-collection)

## Helper components

When building your own UI component using the Media Library Pro, you can also make use of these helper components.

### DropZone

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue/src/DropZone.vue)

TODO screenshot
TODO description

### InvalidMedia

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue/src/InvalidMedia.vue)

TODO screenshot
TODO description

### MediaFormValues

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue/src/MediaFormValues.vue)

TODO screenshot
TODO description

### PreviewImage

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-vue/src/PreviewImage.vue)

TODO screenshot
TODO description

## Props

| prop name (Vue)   | Default value | Description |
| ----------------- | ------------- | ----------- |
| initial-media     |               |             |
| upload-endpoint   |               |             |
| validation-errors |               |             |
| validation        |               |             |
| multiple          |               |             |
| before-upload     |               |             |
| after-upload      |               |             |
| @change           |               |             |

TODO @is-ready-to-submit-change and the "is something uploading" listener

TODO expand on the prop list, look into examples of how other packages do this etc
