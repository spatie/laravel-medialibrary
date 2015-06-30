<?php

namespace Spatie\MediaLibrary\Helpers;

class File
{
    public static function renameInDirectory($fileNameWithDirectory, $newFileNameWithoutDirectory)
    {
        $targetFile = pathinfo($fileNameWithDirectory, PATHINFO_DIRNAME) . '/' . $newFileNameWithoutDirectory;

        dd($targetFile);

        rename($fileNameWithDirectory, pathinfo($fileNameWithDirectory, PATHINFO_DIRNAME) . '/' . $newFileNameWithoutDirectory);

        return $targetFile;
    }
}