<?php

namespace Spatie\MediaLibrary\Uploads\Rules;

use Illuminate\Contracts\Validation\Rule;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;
use Illuminate\Contracts\Session\Session;

class MediaBelongsToSession implements Rule
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
        $mediaClass = config('medialibrary.media_model');

        if (! $media = $mediaClass::find($value)) {

            return false;
        }

        return $media->belongsToSession(session()->getId());
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
