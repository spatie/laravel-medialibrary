---
title: Creating custom React components
weight: 8
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
        :validation-errors="validationErrors"
        :upload-endpoint="uploadEndpoint"
        :validation="validation"
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
    import { MediaLibraryRenderless } from 'medialibrary-pro-vue';

    export default{
        components: { MediaLibraryRenderless },
    }
</script>
```

You can find a full list of props [at the bottom of this page](TODO-link).

## React

For the React components, you can use `useMediaLibrary`. It hooks into the Media Library Pro JavaScript core. This makes it very easy to use in functional components. If you are building a class component, there are several tutorials available online on how to wrap a custom hook in a higher-order component for use in class components.

For more extensive examples, [see the pre-built UI components on GitHub](TODO-link:#examples)

### Getting started

```jsx
import * as React from "react";
import { useMediaLibrary } from "medialibrary-pro-react";

export default function MediaLibraryAttachment() {
    const {
        state,
        getImgProps,
        getFileInputProps,
        getDropZoneProps,
        removeMedia,
        setOrder,
        replaceMedia,
        getErrors,
        clearObjectErrors,
        clearInvalidMedia,
        isReadyToSubmit,
    } = useMediaLibrary({
        initialMedia: initialValue,
        validationErrors,
        uploadEndpoint,
        validation,
        multiple,
        beforeUpload,
        afterUpload,
        onChange,
    });

    return (
        <div>
            <input type="file" multiple {...getFileInputProps()} />
        </div>
    );
}
```

You can find a full list of props [at the bottom of this page](TODO-link).

## Examples

For extensive examples you can have a look at the source of the pre-built UI components:

-   [Vue attachment component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/ui/medialibrary-pro-vue-attachment)
-   [Vue collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/ui/medialibrary-pro-vue-collection)
-   [React collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/ui/medialibrary-pro-react-attachment)
-   [React collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/ui/medialibrary-pro-react-collection)

## Helper components

When building your own UI component using the Media Library Pro, you can also make use of these helper components.

### DropZone

See code on GitHub: [Vue](https://github.com/spatie/laravel-medialibrary-pro/blob/master/ui/medialibrary-pro-vue/src/DropZone.vue) / [React](https://github.com/spatie/laravel-medialibrary-pro/blob/master/ui/medialibrary-pro-react/src/DropZone.tsx)

TODO screenshot
TODO description

### InvalidMedia

See code on GitHub: [Vue](https://github.com/spatie/laravel-medialibrary-pro/blob/master/ui/medialibrary-pro-vue/src/InvalidMedia.vue) / [React](https://github.com/spatie/laravel-medialibrary-pro/blob/master/ui/medialibrary-pro-react/src/InvalidMedia.tsx)

TODO screenshot
TODO description

### MediaFormValues

See code on GitHub: [Vue](https://github.com/spatie/laravel-medialibrary-pro/blob/master/ui/medialibrary-pro-vue/src/MediaFormValues.vue) / [React](https://github.com/spatie/laravel-medialibrary-pro/blob/master/ui/medialibrary-pro-react/src/MediaFormValues.tsx)

TODO screenshot
TODO description

### PreviewImage

See code on GitHub: [Vue](https://github.com/spatie/laravel-medialibrary-pro/blob/master/ui/medialibrary-pro-vue/src/PreviewImage.vue) / [React](https://github.com/spatie/laravel-medialibrary-pro/blob/master/ui/medialibrary-pro-react/src/PreviewImage.tsx)

TODO screenshot
TODO description

### useDragula (React only)

See code on GitHub: [React](https://github.com/spatie/laravel-medialibrary-pro/blob/master/ui/medialibrary-pro-react/src/useDragula.ts)

TODO description
TODO explain how to do this in Vue?

## Props

| prop name (Vue)   | prop name (React) | Default value | Description |
| ----------------- | ----------------- | ------------- | ----------- |
| initial-media     | initialMedia      |               |             |
| upload-endpoint   | uploadEndpoint    |               |             |
| validation-errors | validationErrors  |               |             |
| validation        | validation        |               |             |
| multiple          | multiple          |               |             |
| before-upload     | beforeUpload      |               |             |
| after-upload      | afterUpload       |               |             |
| @change           | onChange          |               |             |

TODO @is-ready-to-submit-change and the "is something uploading" listener

TODO expand on the prop list, look into examples of how other packages do this etc
