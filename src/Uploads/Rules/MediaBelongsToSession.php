<?php

namespace Spatie\MediaLibrary\Uploads\Rules;

use Illuminate\Contracts\Validation\Rule;

class MediaBelongsToSession implements Rule
{
    public function passes($attribute, $value): bool
    {
        $mediaClass = config('medialibrary.media_model');

        /** @var \Spatie\MediaLibrary\Models\Media $media */
        if (! $media = $mediaClass::find($value)) {
            return false;
        }

        return $media->belongsToSession(session()->getId());
    }

    public function message(): string
    {
        return 'Could not process this upload.';
    }
}
