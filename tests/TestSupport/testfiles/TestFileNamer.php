<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\testfiles;

use Spatie\MediaLibrary\Support\FileNamer\FileNamer;

class TestFileNamer extends FileNamer
{
    public function getFileName(string $fileName): string
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);

        return 'prefix_' . $fileName . '_suffix';
    }
}
