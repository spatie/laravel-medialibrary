---
title: [Advanced] Creating a custom Vue or React component
weight: 4
---

If you need more options for customisation in your UI, you can extend the core that the UI components are built on. This way, you have all the freedom in what you want your components to look like, but a lot of work is already done for you, like handling component state, error handling and temporary uploads. Both the Vue and React UI components are built on top of the same core, with a language-specific abstraction layer in between that exposes some helper functions. This page will go into detail about these abstraction layers.

These packages also export some helper components that will make building a custom component much easier. These components are also used in the pre-built UI components that ship with the Medialibrary Pro. You can read more about these helper components in the [Helper Components](TODO-link) section

We opted to use a renderless component in Vue and a custom hook in React. With the release of Vue 3, we're considering refactoring the Vue implementation to use the composition API. This will bring along some breaking changes for users of the renderless component.

## Vue

The Vue implementation uses a renderless component that exposes all the functions and values through a slot scope. Like mentioned earlier, this could change in a future version if we decide to refactor the Vue implementation to work with the new composition API that was released in Vue 3.

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
                mediaTranslations,
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

For the React components, we built a custom hook `useMediaLibrary` that hooks into the Medialibrary Pro JavaScript core. This makes it very easy to use in functional components. If you are building a class component, there are several tutorials available online on how to wrap a custom hook in a higher-order component (HOC) for use in class components.

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
        mediaTranslations,
        clearInvalidMedia,
        isReadyToSubmit,
    } = useMediaLibrary({
        initialMedia: initialValue,
        validationErrors,
        uploadEndpoint,
        validation,
        translations,
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

While building the UI components that are packaged with the Medialibrary Pro, we quickly noticed a lot of repeating patterns between the collection and attachment components. This is why we decided to extract some components to the language specific libraries, and export them from here, rather than keeping duplicates in all the separate UI component libraries.

When building your own UI component using the Medialibrary Pro, you can also make use of these components where and if you want, which should make the process a lot easier. Below, you can find a list of the components you can use, including an explanation of their purpose, their usage, and required props. You can also use them as inspiration when building your own UI components from scratch.

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
| translations      | translations      |               |             |
| multiple          | multiple          |               |             |
| before-upload     | beforeUpload      |               |             |
| after-upload      | afterUpload       |               |             |
| @change           | onChange          |               |             |
