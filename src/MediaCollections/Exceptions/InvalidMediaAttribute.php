<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;

class InvalidMediaAttribute extends Exception
{
    public static function duplicateCollection(string $collectionName, string $modelClass): self
    {
        return new static("The media collection `{$collectionName}` is declared more than once via attributes on `{$modelClass}`.");
    }

    public static function unknownCollection(string $conversionName, string $collectionName, string $modelClass): self
    {
        return new static("The media conversion `{$conversionName}` on `{$modelClass}` references unknown collection `{$collectionName}`.");
    }
}
