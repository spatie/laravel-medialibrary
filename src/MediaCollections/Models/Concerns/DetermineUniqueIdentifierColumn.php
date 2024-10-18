<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Concerns;

trait DetermineUniqueIdentifierColumn
{
    public static function determineUniqueIdentifierColumn(): string
    {
        return config('media-library.use_ulid_column') === true ? 'ulid' : 'uuid';
    }
}
