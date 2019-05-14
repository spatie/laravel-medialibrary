<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Spatie\MediaLibrary\File;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollection\MediaCollection;

class FileUnacceptableForCollection extends FileCannotBeAdded
{
    /**
     * @param  File $file
     * @param  MediaCollection $mediaCollection
     * @param  HasMedia|null $hasMedia
     * @return FileUnacceptableForCollection
     */
    public static function create(File $file, MediaCollection $mediaCollection, $hasMedia = null)
    {
        $message = "The file with properties `{$file}` was not accepted into the collection named `{$mediaCollection->name}`";

        if ($hasMedia) {
            $modelType = get_class($hasMedia);
            $message .= " of model `{$modelType}` with id `{$hasMedia->getKey()}`";
        }

        return new static($message);
    }
}
