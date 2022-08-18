<?php

it('will generate the conversion and remove the old file if the file type differs', function () {
    /** @var \Spatie\MediaLibrary\Tests\TestCase $this */
    $this->testModelWithConversionReplacingOriginal->addMedia($this->getTestPng())->toMediaCollection('images');

    expect($this->testModelWithConversionReplacingOriginal->getFirstMediaUrl("images"))->toEqual("/media/1/test.jpg");
    expect($this->testModelWithConversionReplacingOriginal->getFirstMedia("images")->file_name)->toEqual("test.jpg");

    $path = $this->testModelWithConversionReplacingOriginal->getFirstMediaPath("images");
    $png = substr($path, 0, -3) . 'png';
    expect($png)->toEndWith('.png');
    expect(file_exists(substr($path, 0, -3) . 'png'))->toBeFalse();
    expect(file_exists($path))->toBeTrue();
});

it('will replace the original file', function () {
    /** @var \Spatie\MediaLibrary\Tests\TestCase $this */
    $this->testModelWithConversionReplacingOriginal->addMedia($this->getTestJpg())->toMediaCollection('images');
    $media = $this->testModelWithConversionReplacingOriginal->getFirstMedia("images");

    expect($media->getUrl())->toEqual("/media/1/test.jpg");
    expect($media->file_name)->toEqual("test.jpg");
    expect($media->hasGeneratedConversion("replace_original"))->toBeFalse();

    [$width, $height] = getimagesize($media->getPath());
    expect($width)->toEqual(200);
    expect($height)->toEqual(165); // Because ratio is preserved
});
