<?php

namespace Spatie\Medialibrary\Exceptions;

use Exception;
use Spatie\MediaLibrary\Media;

class MediaCannotBeUpdated extends Exception
{
    public static function doesNotBelongToCollection(string $collectionName, Media $media)
    {
        return new static("Media id {$media->getKey()} is not part of collection `{$collectionName}`");
    }
}
