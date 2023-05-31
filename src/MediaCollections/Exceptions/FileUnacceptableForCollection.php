<?php

namespace Programic\MediaLibrary\MediaCollections\Exceptions;

use Programic\MediaLibrary\HasMedia;
use Programic\MediaLibrary\MediaCollections\File;
use Programic\MediaLibrary\MediaCollections\MediaCollection;

class FileUnacceptableForCollection extends FileCannotBeAdded
{
    public static function create(File $file, MediaCollection $mediaCollection, HasMedia $hasMedia): self
    {
        $modelType = $hasMedia::class;

        return new static("The file with properties `{$file}` was not accepted into the collection named `{$mediaCollection->name}` of model `{$modelType}` with id `{$hasMedia->getKey()}`");
    }
}
