<?php

use Spatie\MediaLibrary\Tests\TestSupport\TestFileNamer;

uses(ResponsiveImageTest::class);

beforeEach(function () {
    config()->set("media-library.file_namer", TestFileNamer::class);

    $this->fileName = "prefix_test_suffix";
    $this->fileNameWithUnderscore = "prefix_test__suffix";
});

