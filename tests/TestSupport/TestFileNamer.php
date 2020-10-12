<?php

namespace Spatie\MediaLibrary\Tests\TestSupport;

use Spatie\MediaLibrary\Support\FileNamer\FileNamer;

class TestFileNamer extends FileNamer
{

    public function getFileName(string $fileName): string
    {
        return 'testing_file_namer';
    }
}
