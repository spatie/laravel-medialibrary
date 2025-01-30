<?php

namespace Spatie\MediaLibrary\Support\FileRemover;

use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidFileRemover;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileRemoverFactory
{
    public static function create(Media $media): FileRemover
    {
        $fileRemoverClass = config('media-library.file_remover_class');

        static::guardAgainstInvalidFileRemover($fileRemoverClass);

        return app($fileRemoverClass);
    }

    protected static function guardAgainstInvalidFileRemover(string $fileRemoverClass): void
    {
        if (! class_exists($fileRemoverClass)) {
            throw InvalidFileRemover::doesntExist($fileRemoverClass);
        }

        if (! is_subclass_of($fileRemoverClass, FileRemover::class)) {
            throw InvalidFileRemover::doesNotImplementFileRemover($fileRemoverClass);
        }
    }
}
