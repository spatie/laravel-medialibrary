---
title: Handling uploads with Vue or React
weight: 4
---

If you're using Vue or React, Medialibrary Pro provides some UI components that look beautiful and work out of the box with the medialibrary backend. They're really easy to set up, and have a lot of features already built-in, like temporary uploads, custom property inputs, frontend validation, i18n and .

If you want to heavily customise the way the UI components look, feel or work, you can also extend the core that the UI components are built on. This way, a lot of work is already done for you, like handling component state, error handling and temporary uploads. Both the Vue and React UI components are built on top of the same core, with a language-specific abstraction layer in between. More on this later (TODO advanced setup).

Before we really get started: if you get stuck at any point during these tutorials, don't hesitate to create an issue on the GitHub repository. We'll do our best to get you running, and we'll clear up the part of the documentation that might have been unclear or incomplete.

## Preparing your Laravel app

The Vue and React components expect this route to be registered

```php
//somewhere in a routes file

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

You can import the components them from the vendor folder:
(TODO adriaan, where should these be imported? What if I use mix/webpack...)

**Vue**

To use the component in your blade templates, simply import the component in your app.js file, and add it to your `components` object.

```js
import MediaLibraryAttachment from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-attachment";

var app = new Vue({
    components: { MediaLibraryAttachment },
});
```

You can now use it in any .blade.php file in your application:

```html
<div>
    <media-library-attachment
        name="avatar"
        upload-endpoint="temp-upload"
    ></media-library-attachment>
</div>
```

**React**

Simply import the medialibrary component in your custom component, and use it in your JSX:

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

The attachment component is meant to be used to upload one or multiple images with little or no extra information. Images are displayed in a grid, with optional extra properties (e.g. the image size) or input fields (e.g. the image name) displayed right below them. (TODO check if this is still correct by the time we launch)

See [Props](TODO frontend-setup-props) for a complete list of all props.

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

### Automatically submitting after uploading

When creating a stand-alone avatar field, it would be nice to have it save automatically after your image has finished uploaded. This can easily be done by triggering a form submit after the image has uploaded, using the `after-upload`/`afterUpload` prop:

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

The collection component can be used to upload multiple images with some custom properties, like alt tags, a caption or tags. This component usually won't be used in a public-facing area.

See [Props](TODO frontend-setup-props) for a complete list of all props.

**Vue**

```js
import MediaLibraryCollection from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-collection";
```

**React**

```js
import MediaLibraryCollection from "../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-react-collection";
```

If you are using TypeScript and you get type errors at this point, check the [Troubleshooting guide](troubleshooting#cannot-find-name-describe-cannot-find-name-test) for a possible fix.

### Basic setup

The basic setup of the collection component is very similar to the attachment component:

**Vue**

```html
<media-library-collection
    name="media"
    :initial-value="initialValue"
    :validation-errors="validationErrors"
></media-library-collection>
```

**React**

```jsx
<MediaLibraryCollection
    name="media"
    initialValue={values.media}
    validationErrors={validationErrors}
></MediaLibraryCollection>
```

To add custom properties, we can use the `afterItems` slot in Vue or the `afterItems` render prop in React:
TODO update name of render prop/slot

**Vue**

```html
<media-library-collection
    name="media"
    :validation="{ accept: ['image/png', 'image/jpeg'], maxSize: 500000 }"
    :initial-value="initialValue"
    upload-endpoint="temp-upload"
    :validation-errors="validationErrors"
>
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
        <div class="mb-2">
            <input
                placeholder="image name"
                class="border rounded"
                v-bind="getNameInputProps()"
                v-on="getNameInputListeners()"
            />
            <p
                v-for="error in getNameInputErrors()"
                :key="error"
                class="text-red-500"
            >
                {{ error }}
            </p>
        </div>

        <div class="mb-2">
            <input
                placeholder="tags (custom property)"
                class="border rounded"
                v-bind="getCustomPropertyInputProps('tags')"
                v-on="getCustomPropertyInputListeners('tags')"
            />
            <p
                v-for="error in getCustomPropertyInputErrors('tags')"
                :key="error"
                class="text-red-500"
            >
                {{ error }}
            </p>
        </div>

        <div class="mb-2">
            <input
                placeholder="caption (custom property)"
                class="border rounded"
                v-bind="getCustomPropertyInputProps('caption')"
                v-on="getCustomPropertyInputListeners('caption')"
            />
            <p
                v-for="error in getCustomPropertyInputErrors('caption')"
                :key="error"
                class="text-red-500"
            >
                {{ error }}
            </p>
        </div>
    </template>
</media-library-collection>
```

**React**

```jsx
<MediaLibraryCollection
    name="media"
    initialValue={values.media}
    uploadEndpoint="temp-upload"
    validation={{ accept: ["image/png", "image/jpeg"], maxSize: 500000 }}
    validationErrors={validationErrors}
    afterItems={({
        getCustomPropertyInputProps,
        getCustomPropertyInputErrors,
        getNameInputProps,
        getNameInputErrors,
    }) => (
        <>
            <div className="mb-2">
                <input
                    className="border rounded"
                    placeholder="image name"
                    {...getNameInputProps()}
                />
                {getNameInputErrors().map((error) => (
                    <p key={error} className="text-red-500">
                        {error}
                    </p>
                ))}
            </div>

            <div className="mb-2">
                <input
                    className="border rounded"
                    placeholder="tags"
                    {...getCustomPropertyInputProps("tags")}
                />
                {getCustomPropertyInputErrors("tags").map((error) => (
                    <p key={error} className="text-red-500">
                        {error}
                    </p>
                ))}
            </div>

            <div className="mb-2">
                <input
                    className="border rounded"
                    placeholder="caption"
                    {...getCustomPropertyInputProps("caption")}
                />
                {getCustomPropertyInputErrors("caption").map((error) => (
                    <p key={error} className="text-red-500">
                        {error}
                    </p>
                ))}
            </div>
        </>
    )}
></MediaLibraryCollection>
```

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

The components keep track of whether they're ready
TODO

## Props

| prop name (Vue)   | prop name (React) | Default value   | Description                                                                                                                 |
| ----------------- | ----------------- | --------------- | --------------------------------------------------------------------------------------------------------------------------- |
| name              | name              | /               |                                                                                                                             |
| initial-value     | initialValue      | `[]`            |                                                                                                                             |
| upload-endpoint   | uploadEndpoint    | `"temp-upload"` |                                                                                                                             |
| validation        | validation        | `undefined`     |                                                                                                                             |
| translations      | translations      | `{}`            |                                                                                                                             |
| validation-errors | validationErrors  | `undefined`     |                                                                                                                             |
| before-upload     | beforeUpload      | `undefined`     |                                                                                                                             |
| after-upload      | afterUpload       | `undefined`     |                                                                                                                             |
| drag-enabled      | dragEnabled       | `true`          | Allows the user to drag images to change their order, this will be reflected by a zero-based `order` attribute in the value |
| @change           | onChange          | `undefined`     |                                                                                                                             |
