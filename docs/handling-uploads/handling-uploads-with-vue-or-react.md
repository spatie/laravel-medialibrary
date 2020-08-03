---
title: Handling uploads with Vue or React
weight: 4
---

If you're using Vue or React, Medialibrary Pro provides UI components that look beautiful and work out of the box with the medialibrary backend. They're simple to set up, and include a lot of features: temporary uploads, custom property inputs, frontend validation, i18n and error handling.

The look and feel of UI components [can be customized](TODO-LINK:creating-a-custom-react-or-vue-component).

## Preparing your Laravel app

The package contains a controller `Spatie\MediaLibraryPro\Http\Controllers\UploadController` that handles all incoming uploads from Vue and React component. You should register a route that uses that controller.

```php
//somewhere in a Laravel routes file

use Spatie\MediaLibraryPro\Http\Controllers\UploadController;

Route::post('media-library-upload-components', UploadController::class);
```

`media-library-upload-components` is the default URL that the components expect. You can choose any URL if you'd like. If you use a custom URL for this route, you should pass your URL to the `upload-endpoint` prop of your Vue or React component.

Here is an example:

```html
<media-library-attachment
    name="avatar"
    upload-endpoint="temp-upload"
></media-library-attachment>
```

## Including the components

The UI components should work out of the box. You shouldn't have to change anything about your Laravel Mix or Webpack configuration to make them work. You can import the components from the vendor folder like this:

**Vue**

To use a component in your blade templates, simply import the component in your app.js file, and add it to your `components` object.

```js
import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-attachment";

var app = new Vue({
    components: { MediaLibraryAttachment },
});
```

You can now use it in any `.blade.php` file in your application:

```html
<div>
    <media-library-attachment name="avatar"></media-library-attachment>
</div>
```

**React**

Import the medialibrary component in your custom component, and use it in JSX:

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

### Creating your first uploader

TODO freek: how to export this format from the server

If you want to pass an initial value to your uploader (e.g. in case of avatar uploaders and backoffice media managers), make sure you use the below format. This is the same format the components use for images that you upload. The components automatically create hidden input fields that keep track of these values. If you want to submit your form asynchronically (e.g. using ajax/axios/…), you can also subscribe to this value by listening to the `@change` event in Vue `onChange` prop in React.

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
        thumbnail: "https://example.com/cat.jpeg",
    },
    {
        uuid: "efgh",
        order: 1,
        name: "dog",
        custom_properties: {
            alt: "picture of a dog",
            tags: ["pet", "paws", "woof"],
        },
        thumbnail: "https://example.com/dog.jpeg",
    },
];
```

This is what that looks like as a TypeScript type:

```ts
Array<{
    uuid: string;
    order: number;
    name: string;
    custom_properties: {
        [key: string]: any;
    };
    thumbnail: null | string;
}>;
```

**Vue**

```html
<form>
    <media-library-attachment
        name="avatar"
        :validation="{ accept: ['image/png', 'image/jpeg'], maxSize: 500000 }"
        :initial-value="user.avatar"
        upload-endpoint="temp-upload"
        :validation-errors="validationErrors"
        @change="doSomethingWithValue($event)"
    ></media-library-attachment>

    <button>Submit</button>
</form>
```

**React**

```jsx
<form>
    <MediaLibraryAttachment
        name="media"
        validation={{ accept: ["image/png", "image/jpeg"], maxSize: 500000 }}
        initialValue={user.avatar}
        uploadEndpoint="temp-upload"
        validationErrors={validationErrors}
        onSubmit={(value) => doSomethingWithValue(value)}
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

The attachment component can upload one or multiple images with little or no extra information. Images are displayed in a grid, with optional extra properties (e.g. the image size) or input fields (e.g. the image name) displayed right below them. (TODO check if this is still correct by the time we launch)

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

### TODO afterItems render prop

### Automatically submitting after uploading

Using the `after-upload`/`afterUpload` prop, you can submit the form after the upload has been completed.

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

The collection component can upload multiple images with some custom properties, like alt tags, a caption or tags. This component usually won't be used in a public-facing area, but rather in a backoffice environment.

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

### The render slot

To add custom properties, you can use the `afterItems` slot in Vue or the `afterItems` render prop in React. You get a couple of methods back that you can use to easily populate your input elements with the required props, and to display any validation errors that may occur when submitting.
(TODO update name of render prop/slot)

**Vue**

```html
<media-library-collection name="media">
    <template
        slot="afterItems"
        slot-scope="{
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
    afterItems={({
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

If you don't want to use traditional form submits to send your data to the backend, you can also easily keep track of the current value of the component. The syntax is the same for all UI components:

**Vue**

```html
<template>
    <div>
        <media-library-attachment
            …
            @change="onChange"
        ></media-library-attachment>

        // or
        // <media-library-collection
        //    …
        //    @change="onChange"
        // ></media-library-collection>

        <button @click="submitForm">Submit</button>
    </div>
</template>

<script>
    import Axios from 'axios';

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
                Axios
                    .post('endpoint', { media: this.media })
                    .catch(error => this.validationErrors = error.data.errors);
            }
        }
    }
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
                onChange={setMedia}
            ></MediaLibraryAttachment>

            <button onClick={submitForm}>Submit</button>
        </>
    );
}
```

## Checking the upload state

The components keep track of whether they're ready to be submitted, you can use this to, for example, disable a submit button while a file is still uploading, or when there are frontend validation errors. This value can be tracked by listening to a `is-ready-to-submit-change` event on the components (`onIsReadyToSubmitChange` in React):

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

TODO mention the "is something uploading" listener, e.g. in case people aren't interested by validation errors?

## Validation rules

TODO (not completely ready in frontend yet)
Also mention beforeUpload prop for custom validation

## Translations

The UI components show some text, like for validation rules, errors and hints. If your website is displayed in a different language than English, or you'd like the text to be displayed in the user's language, you can add your own translations through the `translations` prop.

Below, you can see the default translations object. You don't have to copy the entire object, depending on your use case. Any translations that aren't found, will be substituted by their English default. Some of these strings will be displayed in front of or after a value, so make sure to keep this in mind while writing your replacement strings.

```js
{
    hint: {
        singular: 'Drag a file or click to set media',
        plural: 'Drag some files or click to add media',
    },
    invalidDrag: {
        singular: 'This file has the incorrect file type and will not be uploaded',
        plural: '(Some of) these file have the incorrect file type and will not be uploaded',
    },
    replace: 'Drag a file or click to replace media',
    fileTypeNotAllowed: 'File type not allowed. Allowed file types:',
    tooLarge: 'File too large, max',
    previewGenerateError: 'Error while generating preview',
    tryAgain: 'please try uploading this file again',
    somethingWentWrong: 'Something went wrong while uploading this file',
    maxSize: 'Maximum file size:',
}
```

**Vue**

```html
<media-library-attachment
    name="avatar"
    :translations="{ somethingWentWrong: 'whoops!' }"
></media-library-attachment>
```

**React**

```jsx
<MediaLibraryAttachment
    name="avatar"
    translations={{ tooLarge: "That's a lot of bytes! I can only handle" }}
></MediaLibraryAttachment>
```

## Props

| prop name (Vue)            | prop name (React)       | Default value                       | Description                                                                                                                 |
| -------------------------- | ----------------------- | ----------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| name                       | name                    | /                                   |                                                                                                                             |
| initial-value              | initialValue            | `[]`                                |                                                                                                                             |
| upload-endpoint            | uploadEndpoint          | `"media-library-upload-components"` |                                                                                                                             |
| translations               | translations            | `{}`                                | Refer to [translations](TODO-link) section                                                                                  |
| validation                 | validation              | `undefined`                         | Refer to [validation](TODO-link) section                                                                                    |
| validation-errors          | validationErrors        | `undefined`                         | The standard Laravel validation error object                                                                                |
| before-upload              | beforeUpload            | `undefined`                         |                                                                                                                             |
| after-upload               | afterUpload             | `undefined`                         |                                                                                                                             |
| drag-enabled               | dragEnabled             | `true`                              | Allows the user to drag images to change their order, this will be reflected by a zero-based `order` attribute in the value |
| @change                    | onChange                | `undefined`                         |                                                                                                                             |
| @is-ready-to-submit-change | onIsReadyToSubmitChange | `undefined`                         | Refer to [Checking the upload state](TODO-link) section                                                                     |

TODO expand on the prop list, look into examples of other packages etc
