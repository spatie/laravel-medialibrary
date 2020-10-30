<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Spatie\MediaLibrary\Tests\TestSupport\TestFileNamer;

class ResponsiveImageFileNamerTest extends ResponsiveImageTest
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set("media-library.file_namer", TestFileNamer::class);

        $this->fileName = "prefix_test_suffix";
        $this->fileNameWithUnderscore = "prefix_test__suffix";
    }
}
