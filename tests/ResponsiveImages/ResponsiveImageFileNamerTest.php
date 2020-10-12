<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Spatie\MediaLibrary\Tests\TestSupport\TestFileNamer;

class ResponsiveImageFileNamerTest extends ResponsiveImageTest
{
    /**
     * Runs the same set of tests as ResponsiveImageTest, but with a different
     * File namer.
     */
    public function setUp(): void
    {
        parent::setUp();
        \Config::set("media-library.file_namer", TestFileNamer::class);
        $this->file_name = "testing_file_namer";
    }
}
