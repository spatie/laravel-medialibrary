---
title: Handling uploads with Vue or React
weight: 6
---

Media Library Pro provides beautiful UI components for Vue and React. They work out of the box and pack a lot of features: temporary uploads, custom property inputs, frontend validation, i18n, and robust error handling.

## Preparing your Laravel app

The Media Library Pro package ships with a controller that handles all incoming uploads from Vue and React component. To get started, register that controller in a route.

```php
// routes/web.php

use Spatie\MediaLibraryPro\Http\Controllers\UploadController;

Route::post('media-library-upload-components', UploadController::class);
```

The UI components post data to `/media-library-upload-components` by default. If registered the controller on a different URL, pass it to the `upload-endpoint` prop of your Vue or React components.

```html
<media-library-attachment
    name="avatar"
    upload-endpoint="temp-upload"
></media-library-attachment>
```

## Including the components

The UI components work out of the box with Laravel Mix.

**Vue**

To use a component in your Blade templates, simply import the component in your app.js file, and add it to your `components` object.

```js
import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-attachment";

const app = new Vue({
    el: '#app',

    components: {
        MediaLibraryAttachment
    },
});
```

You can now use it in any `.blade.php` file in your application:

```html
<div id="app">
    <media-library-attachment name="avatar"></media-library-attachment>
</div>
```

**React**

Import the medialibrary component in your custom component, and use it like this:

```jsx
import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-react-attachment";

export default function MyImageUploader() {
    return (
        <div>
            <MediaLibraryAttachment name="avatar" />
        </div>
    );
}
```

### Passing an initial value to your components

TODO freek: how to export this format from the server

If you want to pass an initial value to your uploader (e.g. in case of avatar uploaders, backoffice media managers, …), use the below format. The components automatically create hidden input fields that keep track of these values, so these values will be sent along with a form submit automatically.

```js
[
    {
        uuid: "abcd",
        order: 0,
        name: "cat",
        custom_properties: {
            alt: "picture of a cat",
            tags: ["pet", "whiskers", "meow"],
        },
        preview_url: "https://example.com/cat.jpeg",
        extension: "jpeg",
        size: 256,
    },
    {
        uuid: "efgh",
        order: 1,
        name: "dog",
        custom_properties: {
            alt: "picture of a dog",
            tags: ["pet", "paws", "woof"],
        },
        preview_url: "https://example.com/dog.jpeg",
        extension: "jpeg",
        size: 256,
    },
];
```

This is what that looks like as a TypeScript type:

```ts
type initialMedia = Array<{
    uuid: string;
    order: number;
    name: string;
    custom_properties: { [key: string]: any };
    preview_url: null | string;
    extension?: string;
    size?: number;
}>;
```

Alternatively, you can choose to set the initial value in the same way the frontend components submit the value, which is this:

```ts
type initialMedia = {
    [uuid: string]: {
        uuid: string;
        order: number;
        name: string;
        custom_properties: { [key: string]: any };
        preview_url: null | string;
        extension?: string;
        size?: number;
    };
};
```

**Vue**

```html
<form>
    <media-library-attachment
        name="avatar"
        :initial-value="user.avatar"
        upload-endpoint="temp-upload"
        :validation="{ accept: ['image/png', 'image/jpeg'], maxSize: 500000 }"
        :validation-errors="validationErrors"
    ></media-library-attachment>

    <button>Submit</button>
</form>
```

**React**

```jsx
<form>
    <MediaLibraryAttachment
        name="avatar"
        initialValue={user.avatar}
        uploadEndpoint="temp-upload"
        validation={{ accept: ["image/png", "image/jpeg"], maxSize: 500000 }}
        validationErrors={validationErrors}
    ></MediaLibraryAttachment>

    <button>Submit</button>
</form>
```

The value of `validationErrors` should just be the error object that Laravel returns from a form submit. It can be accessed in Blade like this:

```php
{!! $errors->isEmpty() ? '{}' : $errors !!}
```

## Attachment component

TODO: screenshot of attachment component without value
TODO: screenshot of multiple attachment component with 2 or 3 images as value

The attachment component can upload one or multiple images with little or no extra information.

See [Props](TODO-link:frontend-setup-props) for a complete list of all props.

**Vue**

```html
<template>
    <form ref="avatar-form">
        <media-library-attachment name="avatar" />

        <button>Submit</button>
    </form>
</template>

<script>
    import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-attachment";

    export default {
        components: { MediaLibraryAttachment },
    };
</script>
```

**React**

```jsx
import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-react-attachment";

export default function AvatarForm() {
    return (
        <form>
            <MediaLibraryAttachment name="avatar"></MediaLibraryAttachment>

            <button>Submit</button>
        </form>
    );
}
```

### TODO propertiesView render prop

### Automatically submitting after uploading

Using the `after-upload`/`afterUpload` prop, you can submit the form after the upload has been completed:

**Vue**

```html
<template>
    <form ref="avatar-form">
        <media-library-attachment name="avatar after-upload="submitAvatarForm"
        />
    </form>
</template>

<script>
    import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-attachment";

    export default {
        components: { MediaLibraryAttachment },

        methods: {
            submitAvatarForm({ success }) {
                if (success) {
                    this.$nextTick(() => {
                        this.$refs.form.submit();
                        // Or track the value using the @change listener on the component and submit with axios/fetch/…
                    });
                }
            },
        },
    };
</script>
```

**React**

```jsx
import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-react-attachment";

export default function AvatarForm() {
    const formRef = useRef(null);

    function afterMediaUpload({ success }) {
        if (success) {
            formRef.current.submit();
            // Or track the value using the onChange prop on the component and submit with axios/fetch/…
        }
    }

    return (
        <form ref={formRef}>
            <MediaLibraryAttachment
                name="avatar"
                afterUpload={afterMediaUpload}
            ></MediaLibraryAttachment>

            <button>Submit</button>
        </form>
    );
}
```

## Collection component

TODO: screenshot of collection component with some images and custom properties. Maybe also with a validation error.

The collection component can upload multiple images with some custom properties (e.g. an alt tag, a caption, keywords, …). This component usually won't be used in a public-facing area, but rather in a backoffice environment.

See [Props](TODO-link:frontend-setup-props) for a complete list of all props.

**Vue**

```js
import MediaLibraryCollection from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-collection";
```

**React**

```js
import MediaLibraryCollection from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-react-collection";
```

If you are using TypeScript and you get type errors at this point, read the [Troubleshooting guide](troubleshooting#cannot-find-name-describe-cannot-find-name-test) for a possible fix.

### Basic setup

The basic setup of the collection component is pretty much identical to the attachment component:

**Vue**

```html
<media-library-collection name="media"></media-library-collection>
```

**React**

```jsx
<MediaLibraryCollection name="media"></MediaLibraryCollection>
```

### TODO propertiesView render prop

By default, the file extension, size and a download button will be rendered. You can overwrite the slot/render prop to change this behavior.

### Adding custom property input fields

To add custom property input fields, you can use the `fieldsView` slot in Vue or the `fieldsView` render prop in React. You get a couple of methods back that you can use to easily populate your input elements with the required props, and to display any validation errors that may occur when submitting.

By default, a name input field will already be rendered. You can overwrite the slot/render prop to change this behavior.

**Vue**

```html
<media-library-collection name="media">
    <template
        slot="fieldsView"
        slot-scope="{
            object,
            getCustomPropertyInputProps,
            getCustomPropertyInputListeners,
            getCustomPropertyInputErrors,
            getNameInputProps,
            getNameInputListeners,
            getNameInputErrors,
        }"
    >
        <input
            placeholder="name"
            v-bind="getNameInputProps()"
            v-on="getNameInputListeners()"
        />
        <p v-for="error in getNameInputErrors()" :key="error">{{ error }}</p>

        <input
            placeholder="tags"
            v-bind="getCustomPropertyInputProps('tags')"
            v-on="getCustomPropertyInputListeners('tags')"
        />
        <p v-for="error in getCustomPropertyInputErrors('tags')" :key="error">
            {{ error }}
        </p>

        <input
            placeholder="alt tag"
            v-bind="getCustomPropertyInputProps('alt')"
            v-on="getCustomPropertyInputListeners('alt')"
        />
        <p v-for="error in getCustomPropertyInputErrors('alt')" :key="error">
            {{ error }}
        </p>

        <input
            placeholder="caption"
            v-bind="getCustomPropertyInputProps('caption')"
            v-on="getCustomPropertyInputListeners('caption')"
        />
        <p
            v-for="error in getCustomPropertyInputErrors('caption')"
            :key="error"
        >
            {{ error }}
        </p>
    </template>
</media-library-collection>
```

**React**

```jsx
<MediaLibraryCollection
    name="media"
    fieldsView={({
        object,
        getCustomPropertyInputProps,
        getCustomPropertyInputErrors,
        getNameInputProps,
        getNameInputErrors,
    }) => (
        <>
            <input placeholder="image name" {...getNameInputProps()} />
            {getNameInputErrors().map((error) => (
                <p key={error} className="text-red-500">
                    {error}
                </p>
            ))}

            <input
                placeholder="tags"
                {...getCustomPropertyInputProps("tags")}
            />
            {getCustomPropertyInputErrors("tags").map((error) => (
                <p key={error} className="text-red-500">
                    {error}
                </p>
            ))}

            <input
                placeholder="caption"
                {...getCustomPropertyInputProps("caption")}
            />
            {getCustomPropertyInputErrors("caption").map((error) => (
                <p key={error} className="text-red-500">
                    {error}
                </p>
            ))}
        </>
    )}
></MediaLibraryCollection>
```

See the [Props section](TODO-link:frontend-setup-props) for a complete list of all props.

## Asynchronously submit data

If you don't want to use traditional form submits to send your data to the backend, you will have to keep track of the current value of the component using the `onChange` handler. The syntax is the same for all UI components:

**Vue**

```html
<template>
    <div>
        <media-library-attachment
            …
            :validation-errors="validationErrors"
            @change="onChange"
        ></media-library-attachment>

        <media-library-collection
            …
            :validation-errors="validationErrors"
            @change="onChange"
        ></media-library-collection>

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

**React**

```jsx
import Axios from 'axios';

export function AvatarForm({ values }) {
    const [media, setMedia] = React.useState(values.media);
    const [validationErrors, setValidationErrors] = React.useState({});

    function submitForm() {
        Axios
            .post('endpoint', { media })
            .catch(error => setValidationErrors(error.data.errors));
    }

    return (
        <>
            <MediaLibraryAttachment
                …
                validationErrors={validationErrors}
                onChange={setMedia}
            ></MediaLibraryAttachment>

            <button onClick={submitForm}>Submit</button>
        </>
    );
}
```

## Checking the upload state

The components keep track of whether they're ready to be submitted, you can use this to disable a submit button while a file is still uploading or when there are frontend validation errors. This value can be tracked by listening to a `is-ready-to-submit-change` event on the components in Vue, or `onIsReadyToSubmitChange` in React:

**Vue**

```html
<template>
    <form>
        <media-library-attachment
            name="avatar"
            @is-ready-to-submit-change="isReadyToSubmit = $event"
        ></media-library-attachment>

        <button :disabled="isReadyToSubmit">Submit</button>
    </form>
</template>

<script>
    import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-attachment";

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

**React**

```jsx
import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-react-attachment";

function AvatarComponent() {
    const [isReadyToSubmit, setIsReadyToSubmit] = useState(true);

    return(
        <MediaLibraryAttachment
            name="avatar"
            onIsReadyToSubmitChange={setIsReadyToSubmit}
        ></MediaLibraryAttachment>

        <button disabled={!isReadyToSubmit} onClick={submit}>Submit</button>
    )
}
```

## Validation rules

TODO (not completely ready in frontend yet)
Also mention beforeUpload prop for custom validation

**Vue**

```html
<media-library-attachment name="avatar"></media-library-attachment>
```

**React**

```jsx
<MediaLibraryAttachment name="avatar"></MediaLibraryAttachment>
```

## Props

| prop name (Vue)               | prop name (React)        | Default value                                       | Description                                                                                                                                                                       |
| ----------------------------- | ------------------------ | --------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| name                          | name                     |                                                     |                                                                                                                                                                                   |
| initial-value                 | initialValue             | `[]`                                                |                                                                                                                                                                                   |
| upload-endpoint               | uploadEndpoint           | `"media-library-upload-components"`                 |                                                                                                                                                                                   |
| validation                    | validation               |                                                     | Refer to [validation](TODO-link) section                                                                                                                                          |
| validation-errors             | validationErrors         |                                                     | The standard Laravel validation error object                                                                                                                                      |
| multiple                      | multiple                 | `false`                                             | Only exists on the `attachment` components                                                                                                                                        |
| max-items                     | maxItems                 | `1` when `multiple` = `false`, otherwise `undefined |                                                                                                                                                                                   |
| max-size-for-preview-in-bytes | maxSizeForPreviewInBytes | `5242880` (5MB)                                     | When an image is added, the component will try to generate a local preview for it. This is done on the main thread, and can freeze the component and/or page for very large files |
| sortable                      | sortable                 | `true`                                              | Only exists on the `collection` components. Allows the user to drag images to change their order, this will be reflected by a zero-based `order` attribute in the value           |
| /                             | setMediaLibrary          |                                                     | Used to set a reference to the mediaLibrary instance, so you can change the internal state of the component. In Vue, this is done by adding a `ref` prop to the component         |
| before-upload                 | beforeUpload             |                                                     | A method that is run right before a temporary upload is started. You can throw an `Error` from this function with a custom validation message                                     |
| after-upload                  | afterUpload              |                                                     | A method that is run right after a temporary upload has completed, `{ success: true, uuid }`                                                                                      |
| @change                       | onChange                 |                                                     |                                                                                                                                                                                   |
| @is-ready-to-submit-change    | onIsReadyToSubmitChange  |                                                     | Refer to [Checking the upload state](TODO-link) section                                                                                                                           |

TODO expand on the prop list, look into examples of other packages etc
