<?php

namespace Spatie\Medialibrary\Exceptions;

use Spatie\MediaLibrary\Media;

class MediaCannotBeUpdated
{
    public static function doesNotBelongToCollection(string $collectionName, Media $media)
    {
        return new static("Media id {$media->getKey()} is not part of collection `{$collectionName}`");
    }
}
