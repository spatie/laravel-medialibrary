---
title: Handling uploads with Vue
weight: 5
---

Media Library Pro provides beautiful UI components for Vue. They pack a lot of features: temporary uploads, custom property inputs, frontend validation, i18n, and robust error handling.

The `MediaLibraryAttachment` component can upload one or more files with little or no extra information. The attachment component is a lightweight solution for small bits of UI like avatar fields.

![Screenshot of the MediaLibraryAttachment Vue component]()

The `MediaLibraryCollection` component can upload multiple files with custom properties. The collection component shines when you need to manage media, like in backoffices.

![Screenshot of the MediaLibraryCollection Vue component]()

If neither of these fit the bill, we've exposed a set of APIs for you to be bold and [roll your own components](#).

## Basic setup

First, the server needs to be able to catch your incoming uploads. Register the Media Library `UploadController` in your routes file.

```php
// routes/web.php

use Spatie\MediaLibraryPro\Http\Controllers\UploadController;

Route::post('media-library-upload-components', UploadController::class);
```

The Vue components post data to `/media-library-upload-components` by default. If registered the controller on a different URL, pass it to the `upload-endpoint` prop.

```html
<media-library-attachment
    name="avatar"
    upload-endpoint="temp-upload"
></media-library-attachment>
```

To use a component in your Blade templates, import the components you plan to use in your `app.js` file, and add them to your main Vue app's `components` object.

```js
import Vue from 'vue';
import MediaLibraryAttachment from '../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-attachment';
import MediaLibraryCollection from '../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-collection';

const app = new Vue({
    el: '#app',

    components: {
        MediaLibraryAttachment,
        MediaLibraryCollection
    }
});
```

You can now use them in any `.blade.php` file in your application.

```blade
{{-- posts/edit.blade.php --}}

<div id="app">
    <form>
        <media-library-attachment name="cover"></media-library-attachment>
        <media-library-collection name="images"></media-library-collection>
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
    import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-attachment";
    import MediaLibraryCollection from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-collection";

    export default {
        components: {
            MediaLibraryAttachment,
            MediaLibraryCollection
        }
    };
</script>
```

## Your first components

The most basic components have a `name` prop. This name will be used to identify the media when it's uploaded to the server.

```html
<form>
    <media-library-attachment name="avatar"></media-library-attachment>
    <media-library-collection  name="downloads"></media-library-collection>
    <button>Submit</button>
</form>
```

If your form modifies an existing set of media, you may pass it through in the `initial-value` prop.

```html
<form>
    <media-library-attachment
        name="avatar"
        :initial-value="@json($post->getMedia('cover'))"
    ></media-library-attachment>

    <media-library-collection
        name="downloads"
        :initial-value="@json($post->getMedia('images'))"
    ></media-library-collection>

    <button>Submit</button>
</form>
```

You'll probably want to validate what gets uploaded. Use the `validation` prop, and don't forget to pass Laravel's validation errors too.

```html
<form>
    <media-library-attachment
        name="avatar"
        :initial-value="@json($post->getMedia('cover'))"
        :validation="{ accept: ['image/png', 'image/jpeg'], maxSize: 500000 }"
        :validation-errors="@json($errors)"
    ></media-library-attachment>

    <media-library-collection
        name="downloads"
        :initial-value="@json($post->getMedia('images'))"
        :validation="{ accept: ['image/png', 'image/jpeg'], maxSize: 500000 }"
        :validation-errors="@json($errors)"
    ></media-library-collection>

    <button>Submit</button>
</form>
```

Under the hood, these components create hidden `input` fields to keep track of the form values on submit.

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
