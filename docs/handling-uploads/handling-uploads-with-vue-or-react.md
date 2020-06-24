---
title: Handling uploads with Vue or React
weight: 4
---

Media Library Pro provides upload components for both Vue and React.

## Preparing your Laravel app

The Vue and React component this route to be registered

```php
//somewhere in a routes file

use Spatie\MediaLibraryPro\Http\Controllers\UploadController;

Route::post('media-library-upload-components', UploadController::class);
```

`media-library-upload-components` is the default URL that the components expect. You can choose any URL if you'd like. If you use a custom URL for this route, you should pass your URL to the `upload-endpoint` prop of a Vue or React component.

Here is an example:

```html
<media-single-component
    name="avatar"
    upload-endpoint="temp-upload"
></media-single-component>
```

## Including the component

You can import the components them from the vendor folder:
(TODO adriaan, where should these be imported? What if I use mix/webpack...)

Vue:

```js
import MediaSingleComponent from '../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-single';
```

React:

```js
import MediaSingleComponent from '../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-react-single';
```

### Avatar in a form

If you have an existing form and want to add an avatar field to it:
TODO adriaan: what is the expected format of `user.avatar`
TODO freek: how to export this format from the server

Vue:

```html
<form>
    <media-single-component
        name="avatar"
        :validation="{ accept: ['image/png', 'image/jpeg'], maxSize: 500000 }"
        :initial-value="user.avatar"
        upload-endpoint="temp-upload"
        :validation-errors="validationErrors"
    ></media-single-component>

    <button>Submit</button>
</form>
```

React:

```jsx
<form>
    <MediaSingleComponent
        name="media"
        validation={{ accept: ['image/png', 'image/jpeg'], maxSize: 500000 }}
        initialValue={user.avatar}
        tempEndpoint="temp-upload"
        validationErrors={validationErrors}
    ></MediaSingleComponent>

    <button>Submit</button>
</form>
```

The value of `validationErrors` should just be the error object that Laravel returns from a form submit. It can be accessed in Blade like this:

```php
{!! $errors->isEmpty() ? '{}' : $errors !!}
```

### Stand-alone avatar component

When creating a stand-alone avatar field, it would be nice not to have to press a Submit button to submit your new image. This can easily be done by triggering a form submit after the image has uploaded:

Vue:

```html
<template>
    <form ref="avatar-form">
        <media-single-component … after-upload="submitAvatarForm"></media-single-component>

        <button>Submit</button>
    </form>
</template>

<script>
    export default {
        methods: {
            submitAvatarForm({ success }) {
                if (success) {
                    this.$nextTick(() => {
                        this.$refs.form.submit();
                    });
                }
            },
        },
    };
</script>
```

React:

```jsx
export default function AvatarForm() {
    const formRef = useRef(null);

    function afterMediaUpload({ success }) {
        if (success) {
            formRef.current.submit();
        }
    }

    return (
        <form ref={formRef}>
            <MediaSingleComponent
                …
                afterUpload={afterMediaUpload}
            ></MediaSingleComponent>

            <button>Submit</button>
        </form>
    );
}
```

## Table component

The bundled UI components are included in the composer package, so you will have to import them from the vendor folder:

Vue:

```js
import MediaTableComponent from '../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-vue-table';
```

React:

```js
import MediaTableComponent from '../../../vendor/spatie/laravel-medialibrary-pro/ui/medialibrary-pro-react-table';
```

If you are using TypeScript and you get type errors at this point, check the [Troubleshooting guide](troubleshooting#cannot-find-name-describe-cannot-find-name-test) for a fix.

### Basic setup

The only difference in code between the table and single components, is that the table component has a render prop with which you can add extra fields to manage the media object's custom properties, e.g. tags, alt attributes, captions ….

Vue:

```html
<media-table-component
    name="media"
    :validation="{ accept: ['image/png', 'image/jpeg'], maxSize: 500000 }"
    :initial-value="initialValue"
    upload-endpoint="temp-upload"
    :validation-errors="validationErrors"
>
    <template
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
            <p v-for="error in getNameInputErrors()" :key="error" class="text-red-500">
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
            <p v-for="error in getCustomPropertyInputErrors('tags')" :key="error" class="text-red-500">
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
            <p v-for="error in getCustomPropertyInputErrors('caption')" :key="error" class="text-red-500">
                {{ error }}
            </p>
        </div>
    </template>
</media-table-component>
```

React:

```jsx
<MediaTableComponent
    name="media"
    initialValue={values.media}
    tempEndpoint="temp-upload"
    validation={{ accept: ['image/png', 'image/jpeg'], maxSize: 500000 }}
    validationErrors={validationErrors}
>
    {({ getCustomPropertyInputProps, getCustomPropertyInputErrors, getNameInputProps, getNameInputErrors }) => (
        <>
            <div className="mb-2">
                <input className="border rounded" placeholder="image name" {...getNameInputProps()} />
                {getNameInputErrors().map((error) => (
                    <p key={error} className="text-red-500">
                        {error}
                    </p>
                ))}
            </div>

            <div className="mb-2">
                <input className="border rounded" placeholder="tags" {...getCustomPropertyInputProps('tags')} />
                {getCustomPropertyInputErrors('tags').map((error) => (
                    <p key={error} className="text-red-500">
                        {error}
                    </p>
                ))}
            </div>

            <div className="mb-2">
                <input className="border rounded" placeholder="caption" {...getCustomPropertyInputProps('caption')} />
                {getCustomPropertyInputErrors('caption').map((error) => (
                    <p key={error} className="text-red-500">
                        {error}
                    </p>
                ))}
            </div>
        </>
    )}
</MediaTableComponent>
```

### Additional props

| prop name (Vue) | prop name (React) | Default value | Description                                        |
| --------------- | ----------------- | ------------- | -------------------------------------------------- |
| drag-enabled    | dragEnabled       | `true`        | Allows the user to drag rows to change their order |

See [Props](frontend-setup-props) for a complete list of all props.

## Asynchronously submit data

If you don't want to use traditional form submits to send your data to the backend, you can also easily keep track of the current value of the component. The syntax is the same for all UI components:

Vue:

```html
<template>
    <div>
        <media-single-component
            …
            @change="onChange"
        ></media-single-component>

        // or
        // <media-table-component
        //    …
        //    @change="onChange"
        // ></media-table-component>

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

React:

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
            <MediaSingleComponent
                …
                onChange={setMedia}
            ></MediaSingleComponent>

            <button onClick={submitForm}>Submit</button>
        </>
    );
}
```

## Props

| prop name (Vue)   | prop name (React) | Default value   | Description |
| ----------------- | ----------------- | --------------- | ----------- |
| name              | name              | /               |             |
| initial-value     | initialValue      | `[]`            |             |
| upload-endpoint     | tempEndpoint      | `"temp-upload"` |             |
| validation        | validation        | `undefined`     |             |
| translations      | translations      | `{}`            |             |
| validation-errors | validationErrors  | `undefined`     |             |
| before-upload     | beforeUpload      | `undefined`     |             |
| after-upload      | afterUpload       | `undefined`     |             |
| @change           | onChange          | `undefined`     |             |
