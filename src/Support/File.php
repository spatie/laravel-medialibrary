<?php

namespace Spatie\MediaLibrary\Support;

use Symfony\Component\Mime\MimeTypes;

class File
{
    public static function getHumanReadableSize(int|float $sizeInBytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        $index = min(count($units) - 1, floor(log(abs($sizeInBytes), 1024)));

        return sprintf('%s %s', round(num: abs($sizeInBytes) / (1024 ** $index), precision: 2), $units[$index]);
    }

    public static function getMimeType(string $path): string
    {
        return (string) MimeTypes::getDefault()->guessMimeType($path);
    }
}
