<?php

namespace Spatie\Medialibrary\Exceptions\FileCannotBeAdded;

use Spatie\Medialibrary\Exceptions\FileCannotBeAdded\FileCannotBeAdded;
use Spatie\Medialibrary\File;
use Spatie\Medialibrary\HasMedia\HasMedia;
use Spatie\Medialibrary\MediaCollection\MediaCollection;

class FileUnacceptableForCollection extends FileCannotBeAdded
{
    public static function create(File $file, MediaCollection $mediaCollection, HasMedia $hasMedia): self
    {
        $modelType = get_class($hasMedia);

        return new static("The file with properties `{$file}` was not accepted into the collection named `{$mediaCollection->name}` of model `{$modelType}` with id `{$hasMedia->getKey()}`");
    }
}
