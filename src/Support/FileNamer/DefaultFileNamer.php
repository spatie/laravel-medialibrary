<?php

namespace Spatie\MediaLibrary\Support\FileNamer;

class DefaultFileNamer extends FileNamer
{
    public function getFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }
}
