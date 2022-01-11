<?php

use Spatie\MediaLibrary\Support\File;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can determine a human readable filesize', function () {
    $this->assertEquals('10 B', File::getHumanReadableSize(10));
    $this->assertEquals('100 B', File::getHumanReadableSize(100));
    $this->assertEquals('1000 B', File::getHumanReadableSize(1000));
    $this->assertEquals('9.77 KB', File::getHumanReadableSize(10000));
    $this->assertEquals('976.56 KB', File::getHumanReadableSize(1_000_000));
    $this->assertEquals('9.54 MB', File::getHumanReadableSize(10_000_000));
    $this->assertEquals('9.31 GB', File::getHumanReadableSize(10_000_000_000));
});

it('can determine the mime type of a file', function () {
    $this->assertEquals('text/x-php', File::getMimeType(__FILE__));
});
