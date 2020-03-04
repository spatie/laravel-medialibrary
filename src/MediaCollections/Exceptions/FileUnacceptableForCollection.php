<?php

namespace Spatie\Medialibrary\MediaCollections\Exceptions;

use Spatie\Medialibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\Medialibrary\MediaCollections\File;
use Spatie\Medialibrary\HasMedia;
use Spatie\Medialibrary\MediaCollections\MediaCollection;

class FileUnacceptableForCollection extends FileCannotBeAdded
{
    public static function create(File $file, MediaCollection $mediaCollection, HasMedia $hasMedia): self
    {
        $modelType = get_class($hasMedia);

        return new static("The file with properties `{$file}` was not accepted into the collection named `{$mediaCollection->name}` of model `{$modelType}` with id `{$hasMedia->getKey()}`");
    }
}
