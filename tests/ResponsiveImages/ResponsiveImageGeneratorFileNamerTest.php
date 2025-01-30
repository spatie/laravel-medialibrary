<?php

use Programic\MediaLibrary\Tests\TestSupport\TestFileNamer;

beforeEach(function () {
    config()->set('media-library.file_namer', TestFileNamer::class);

    $this->fileName = 'prefix_test_suffix';
});
