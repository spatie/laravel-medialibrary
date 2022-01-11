<?php

use Spatie\MediaLibrary\Support\File;

it('can determine a human readable filesize', function () {
    expect(File::getHumanReadableSize(10))->toEqual('10 B');
    expect(File::getHumanReadableSize(100))->toEqual('100 B');
    expect(File::getHumanReadableSize(1000))->toEqual('1000 B');
    expect(File::getHumanReadableSize(10000))->toEqual('9.77 KB');
    $this->assertEquals('976.56 KB', File::getHumanReadableSize(1_000_000));
    $this->assertEquals('9.54 MB', File::getHumanReadableSize(10_000_000));
    $this->assertEquals('9.31 GB', File::getHumanReadableSize(10_000_000_000));
});

it('can determine the mime type of a file', function () {
    expect(File::getMimeType(__FILE__))->toEqual('text/x-php');
});
