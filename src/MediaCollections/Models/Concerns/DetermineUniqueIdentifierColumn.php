<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Concerns;

trait DetermineUniqueIdentifierColumn
{
    public static function determineUniqueIdentifierColumn(): string
    {
        return config('media-library.ulid') ? 'ulid' : 'uuid';
    }
}
