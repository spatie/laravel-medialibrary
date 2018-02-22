<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Spatie\MediaLibrary\File;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollection\MediaCollection;

class FileUnacceptableForCollection extends FileCannotBeAdded
{
    public static function create(File $file, MediaCollection $mediaCollection, HasMedia $hasMedia)
    {
        $modelType = get_class($hasMedia);

        return new static("The file with properties `{$file}` was not accepted into the collection named `{$mediaCollection->name}` of model `{$modelType}` with id `{$hasMedia->getKey()}`");
    }
}
