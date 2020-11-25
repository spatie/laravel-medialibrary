---
title: Creating custom React components
weight: 8
---

Both the Vue and React UI components are built on top of the same core, with a language-specific abstraction layer in between that exposes some helper functions. You can extend the core that the UI components are built on. This allow you to customize the UI. This page will go into detail about these abstraction layers.

To create your own UI components that hook into the Media Library Pro JS core, you can use the `useMediaLibrary` hook in a functional component. If you are building a class component, there are several tutorials available online on how to wrap a custom hook in a higher-order component for use in class components.

For more extensive examples, [see the pre-built UI components on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js)

## Getting started

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
        routePrefix,
        validationRules,
        multiple,
        beforeUpload,
        afterUpload,
        onChange,
    });

    return (
        <div>
            <input type="file" multiple {...getFileInputProps()} />

            {state.media.map((object) => (
                <img key={object.attributes.uuid} {...getImgProps(object)} />
            ))}
        </div>
    );
}
```

You can find a full list of available parameters and exposed variables for the hook [at the bottom of this page](#parameters).

## Examples

For extensive examples you can have a look at the source of the pre-built UI components:

-   [React collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-react-attachment)
-   [React collection component](https://github.com/spatie/laravel-medialibrary-pro/tree/master/resources/js/media-library-pro-react-collection)

## Helper components

When building your own UI component using the Media Library Pro, you can also make use of these helper components. These are the same components that are used in the UI components.

### DropZone

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/DropZone.tsx)

Renderless component that exposes some props needed to render a file dropzone.

**props**

```ts
{
    validationAccept?: MediaLibrary.Config["validationRules"]["accept"];
    children: ({
        hasDragObject,
        isDropTarget,
    }: {
        hasDragObject: boolean;
        isDropTarget: boolean;
        isValid: boolean;
    }) => React.ReactNode;
    onDrop: (event: React.DragEvent<HTMLDivElement>) => void;
} & React.DetailedHTMLProps<React.HTMLAttributes<HTMLDivElement>, HTMLDivElement>;
```

### HiddenFields

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/HiddenFields.tsx)

Component that renders hidden input fields with the values of the entire MediaLibrary instance's media state. Only needed if you're planning on submitting forms traditionally (not with AJAX).

**props**

```ts
{
    name: string;
    mediaState: MediaLibrary.State["media"];
}
```

### ItemErrors

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/ItemErrors.tsx)

Component that renders the errors for one media object.

**props**

```ts
{
    objectErrors: string[];
    onBack?: (e: React.MouseEvent) => void;
}
```

### ListErrors

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/ListErrors.tsx)

Component that can render the MediaLibrary instance's invalid media (`mediaLibrary.state.invalidMedia`).

**props**

```ts
{
    invalidMedia: MediaLibrary.State["invalidMedia"];
    topLevelErrors?: Array<string>;
    onClear: () => void;
}
```

### Thumb

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/Thumb.tsx)

Component to display a file's thumbnail. If no preview for the file exists, it will attempt to display its extension. Also implements the [Uploader](#uploader) component to replace files.

**props**

```ts
{
    uploadInfo: MediaLibrary.MediaObject["upload"];
    validationRules?: Partial<MediaLibrary.Config["validationRules"]>;
    imgProps: {
        src: string | undefined;
        alt: string;
        extension: string | undefined;
    };
    onReplace: (file: File) => void;
}
```

### Uploader

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/Uploader.tsx)

Component used to upload new media objects, or to replace an existing object's file. Is used by the [Thumb](#thumb) component.

**props**

```ts
{
    add?: boolean;
    uploadInfo?: MediaLibrary.MediaObject["upload"];
    multiple: boolean;
    validationRules?: Partial<MediaLibrary.Config["validationRules"]>;
    maxItems?: number;
    onDrop: (event: React.DragEvent<HTMLDivElement>) => void;
    onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
}
```

### Icons

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/components/Icons.tsx)

Component that sets svg values for the packaged icons, required if you're planning on using the [icon](#icon) component.

### Icon

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/components/Icon.tsx)

Renders an icon. Requires [icons](#icons) to be rendered on the page.

**Props**

```ts
{
    icon: string;
    className?: string;
}
```

### IconButton

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/components/IconButton.tsx)

Renders a button with an icon. Requires [icons](#icons) to be rendered on the page.

**Props**

```ts
{
    icon: string;
    className?: string;
}
```

### useDragula

[See code on GitHub](https://github.com/spatie/laravel-medialibrary-pro/blob/master/resources/js/media-library-pro-react/src/useDragula.ts)

Custom hook to use [Dragula](https://github.com/bevacqua/react-dragula), to allow sorting of files.

**Parameters**

```ts
handleClass?: string
```

## Parameters

| parameter name           | Default value                | Description                                                                                                                                                              |
| ------------------------ | ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| name                     |                              |                                                                                                                                                                          |
| initialValue             | `[]`                         |                                                                                                                                                                          |
| routePrefix              | `"media-library-pro"`        |                                                                                                                                                                          |
| uploadDomain             |                              | Use this if you're uploading your files to a separate (sub)domain, e.g. `files.mydomain.com` (leave out the trailing slash)                                              |
| validationRules          |                              | Refer to the ["validation"](./handling-uploads-with-react#validation-rules) section                                                                                      |
| validationErrors         |                              | The standard Laravel validation error object                                                                                                                             |
| maxItems                 |                              |                                                                                                                                                                          |
| maxSizeForPreviewInBytes |                              |                                                                                                                                                                          |
| translations             |                              | Refer to the ["Translations"](./handling-uploads-with-react#translations) section                                                                                        |
| vapor                    | `false`                      | Set to true if you will deploy your application to Vapor, this enables uploading of the files to S3. [Read more](./handling-uploads-with-react#using-with-laravel-vapor) |
| vaporSignedStorageUrl    | `"vapor/signed-storage-url"` |                                                                                                                                                                          |
| multiple                 | `true`                       |                                                                                                                                                                          |
| beforeUpload             |                              | A method that is run right before a temporary upload is started. You can throw an `Error` from this function with a custom validation message                            |
| afterUpload              |                              | A method that is run right after a temporary upload has completed, `{ success: true, uuid }`                                                                             |
| onChange                 |                              |                                                                                                                                                                          |

## Returned variables

| variable name                | Description                                                                                                                                                                                                                                             |
| ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| mediaLibrary                 | Ref to the MediaLibrary instance                                                                                                                                                                                                                        |
| state                        | The state of the MediaLibrary instance. Includes `media` (an array of currently added files), `invalidMedia` (files that encountered a frontned validation error) and `validationErrors` (backend validation errors mapped to uuids)                    |
| isReadyToSubmit              | Boolean that tells whether the MediaLibrary instance is ready to submit (has no uploads in progress and has no frontend validation errors)                                                                                                              |
| hasUploadsInProgress         | Boolean that tells whether the MediaLibrary instance currently has uploads in progress                                                                                                                                                                  |
| getImgProps                  | Method that expects a media object, and returns an object with props needed for an `img` tag                                                                                                                                                            |
| getNameInputProps            | Method that expects a media object, and returns an object with props needed for an `input type=text` tag that controls an object's name attribute                                                                                                       |
| getNameInputErrors           | Method that expects a media object, and returns an array of backend validation errors for the `name` attribute                                                                                                                                          |
| getCustomPropertyInputProps  | Method that expects a media object, and a custom property name, and returns an object with props needed for an `input` tag that controls that custom property                                                                                           |
| getCustomPropertyInputErrors | Method that expects a media object, and a custom property name, and returns an array of backend validation errors for that custom property                                                                                                              |
| getFileInputProps            | Method that expects a media object, and returns an object with props needed for an `input type=file` tag that controls an object's name attribute                                                                                                       |
| getDropZoneProps             | Method that expects a media object, and returns an object with props needed for a file dropzone (`onDrop`)                                                                                                                                              |
| addFile                      | Method that allows a user to pass in a File to be added to the MediaLibrary instance's media state                                                                                                                                                      |
| removeMedia                  | Method that expects a media object, and removes it from the MediaLibrary instance's media state                                                                                                                                                         |
| setOrder                     | Method that expects an array of uuids, and sets the `order` attribute of the media objects in the media state                                                                                                                                           |
| setProperty                  | Method that expects a media object, a key (`client_preview`, `attributes.preview_url`, `attributes.size`, `attributes.extension` …), and a value, and sets this property on the media object. This method usually shouldn't be used outside of the core |
| setCustomProperty            | Method that expects a media object, a custom property key (`tags`, `caption`, `alt` …), and a value, and sets this property on the media object                                                                                                         |
| replaceMedia                 | Method that expects a media object and a File, and uploads this file and replaces it on the media object                                                                                                                                                |
| getErrors                    | Method that expects a media object, and returns an array of errors that it may have encountered                                                                                                                                                         |
| clearObjectErrors            | Method that expects a media object, and clears its errors                                                                                                                                                                                               |
| clearInvalidMedia            | Method that clears the MediaLibrary instance's invalidMedia state                                                                                                                                                                                       |
