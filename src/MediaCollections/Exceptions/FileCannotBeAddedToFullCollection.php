<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;

class FileCannotBeAddedToFullCollection extends FileCannotBeAdded
{
    public static function create(File $file, MediaCollection $mediaCollection, HasMedia $hasMedia): self
    {
        $modelType = $hasMedia::class;

        $limit = $mediaCollection->collectionSizeLimit;

        return new static("The file with properties `{$file}` could not be added to the collection named `{$mediaCollection->name}` of model `{$modelType}` with id `{$hasMedia->getKey()}` because the collection is full (limit: {$limit}).");
    }
}
