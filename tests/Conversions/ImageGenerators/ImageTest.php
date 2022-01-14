<?php

use Spatie\MediaLibrary\Conversions\ImageGenerators\Image;

it('can convert an image', function () {
    $imageGenerator = new Image();

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection();

    expect($imageGenerator->canConvert($media))->toBeTrue();

    $imageFile = $imageGenerator->convert($media->getPath());

    expect(mime_content_type($imageFile))->toEqual('image/jpeg');
    expect($media->getPath())->toEqual($imageFile);
});
