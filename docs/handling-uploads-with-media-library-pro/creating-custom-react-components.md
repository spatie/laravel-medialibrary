---
title: Creating custom React components
weight: 8
---

Both the Vue and React UI components are built on top of the same core, with a language-specific abstraction layer in between that exposes some helper functions. You can extend the core that the UI components are built on. This allow you to customize the UI. This page will go into detail about these abstraction layers.

The standard Medialibary UI components are built using helper components. These helper components can be used individually to make custom components. You can read more about the helper components in the [Helper Components](TODO-link) section.

To create your own UI components that hook into the Media Library Pro JS core, you can use the `useMediaLibrary` hook in a functional component. If you are building a class component, there are several tutorials available online on how to wrap a custom hook in a higher-order component for use in class components.

For more extensive examples, [see the pre-built UI components on GitHub](TODO-link:#examples)

### Getting started

```jsx
import * as React from "react";
import { useMediaLibrary } from "media-library-pro-react";

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
        validationRules,
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

-   [React collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-react-attachment)
-   [React collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-react-collection)

## Helper components

When building your own UI component using the Media Library Pro, you can also make use of these helper components.

### DropZone

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/DropZone.tsx)

TODO screenshot
TODO description

### InvalidMedia

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/InvalidMedia.tsx)

TODO screenshot
TODO description

### MediaFormValues

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/MediaFormValues.tsx)

TODO screenshot
TODO description

### PreviewImage

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/PreviewImage.tsx)

TODO screenshot
TODO description

### useDragula (React only)

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/useDragula.ts)

TODO description

## Props

| prop name (React) | Default value | Description |
| ----------------- | ------------- | ----------- |
| initialMedia      |               |             |
| uploadEndpoint    |               |             |
| validationErrors  |               |             |
| validation        |               |             |
| multiple          |               |             |
| beforeUpload      |               |             |
| afterUpload       |               |             |
| onChange          |               |             |

TODO @is-ready-to-submit-change and the "is something uploading" listener

TODO expand on the prop list, look into examples of how other packages do this etc
