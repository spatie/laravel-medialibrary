<?php

namespace Programic\MediaLibrary\MediaCollections\Exceptions;

use Exception;
use Programic\MediaLibrary\MediaCollections\Models\Media;

class MediaCannotBeUpdated extends Exception
{
    public static function doesNotBelongToCollection(string $collectionName, Media $media): self
    {
        return new static("Media id {$media->getKey()} is not part of collection `{$collectionName}`");
    }
}
