<?php

namespace Spatie\MediaLibrary\Helpers;

class Gitignore
{
    public static function createIn($directory)
    {
        $targetFile = $directory.'/.gitignore';

        if (!file_exists($targetFile)) {
            copy(__DIR__.'/../../resources/stubs/gitignore.txt', $targetFile);
        }
    }

    public static function getContents()
    {
        return file_get_contents(__DIR__.'/../../resources/stubs/gitignore.txt');
    }
}
