<?php

namespace Spatie\MediaLibrary\Helpers;

class File
{
    /**
     * Rename a file.
     *
     * @param string $fileNameWithDirectory
     * @param string $newFileNameWithoutDirectory
     *
     * @return string
     */
    public static function renameInDirectory($fileNameWithDirectory, $newFileNameWithoutDirectory)
    {
        $targetFile = pathinfo($fileNameWithDirectory, PATHINFO_DIRNAME).'/'.$newFileNameWithoutDirectory;

        rename($fileNameWithDirectory, $targetFile);

        return $targetFile;
    }
}
