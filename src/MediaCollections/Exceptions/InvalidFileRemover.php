<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;
use Spatie\MediaLibrary\Support\FileRemover\FileRemover;

class InvalidFileRemover extends Exception
{
    public static function doesntExist(string $class): self
    {
        return new static("File remover class `{$class}` doesn't exist");
    }

    public static function doesNotImplementPathGenerator(string $class): self
    {
        $fileRemoverClass = FileRemover::class;

        return new static("File remover class `{$class}` must implement `$fileRemoverClass}`");
    }
}
