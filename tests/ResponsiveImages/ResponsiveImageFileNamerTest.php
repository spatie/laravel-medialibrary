<?php

use Spatie\MediaLibrary\Tests\TestSupport\TestFileNamer;

beforeEach(function () {
    config()->set('medialibrary.file_namer', TestFileNamer::class);

    $this->fileName = 'prefix_test_suffix';
    $this->fileNameWithUnderscore = 'prefix_test__suffix';
});
