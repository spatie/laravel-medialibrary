<?php

namespace Spatie\MediaLibrary\Uploads\Rules;

use Illuminate\Contracts\Validation\Rule;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;
use Illuminate\Contracts\Session\Session;

class TemporaryUploadId implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $temporaryUploadClass = config('medialibrary.uploads.temporary_upload_model');

        return $temporaryUploadClass::findById($value, session()->getId())
            ? true
            : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Could not process this upload.';
    }
}
