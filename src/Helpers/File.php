<?php

namespace Spatie\MediaLibrary\Helpers;

class File
{
    public static function renameInDirectory($fileNameWithDirectory, $newFileNameWithoutDirectory)
    {
        $targetFile = pathinfo($fileNameWithDirectory, PATHINFO_DIRNAME) . '/' . $newFileNameWithoutDirectory;

        rename($fileNameWithDirectory, $targetFile);

        return $targetFile;
    }
}