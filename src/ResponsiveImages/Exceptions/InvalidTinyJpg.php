<?php

namespace Spatie\MediaLibrary\ResponsiveImages\Exceptions;

use Exception;
use Spatie\MediaLibrary\Helpers\File;

class InvalidTinyJpg extends Exception
{
    public static function doesNotExist(string $tinyImageDestinationPath)
    {
        return new static("The expected tiny jpg at `{$tinyImageDestinationPath}` does not exist");
    }

    public static function hasWrongMimeType(string $tinyImageDestinationPath)
    {
        $foundMimeType = File::getMimeType($tinyImageDestinationPath);

        return new static("Expected the file at {$tinyImageDestinationPath} have mimetype `image/jpeg`, but found a file with mimetype `{$foundMimeType}`");
    }
}
