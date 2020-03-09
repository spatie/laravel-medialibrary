<?php

namespace Spatie\MediaLibrary\ResponsiveImages\Exceptions;

use Exception;
use Spatie\MediaLibrary\Support\File;

class InvalidTinyJpg extends Exception
{
    public static function doesNotExist(string $tinyImageDestinationPath): self
    {
        return new static("The expected tiny jpg at `{$tinyImageDestinationPath}` does not exist");
    }

    public static function hasWrongMimeType(string $tinyImageDestinationPath): self
    {
        $foundMimeType = File::getMimeType($tinyImageDestinationPath);

        return new static("Expected the file at {$tinyImageDestinationPath} have mimetype `image/jpeg`, but found a file with mimetype `{$foundMimeType}`");
    }
}
