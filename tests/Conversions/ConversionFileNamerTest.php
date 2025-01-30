<?php

use Programic\MediaLibrary\Tests\TestSupport\TestFileNamer;

it('can use a custom file namer', function () {
    $fileName = 'prefix_test_suffix';

    config()->set('media-library.file_namer', TestFileNamer::class);

    $this
        ->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $path = $this->testModelWithConversion->refresh()->getFirstMediaPath('default', 'thumb');

    expect($path)->toEndWith("{$fileName}---thumb.jpg");
    expect($path)->toBeFile();

    expect($this->testModelWithConversion->getFirstMediaUrl('default', 'thumb'))->toEqual("/media/1/conversions/{$fileName}---thumb.jpg");
});
