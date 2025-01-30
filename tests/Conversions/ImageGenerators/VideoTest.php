<?php

use Programic\MediaLibrary\Conversions\Conversion;
use Programic\MediaLibrary\Conversions\ImageGenerators\Video;

it('can convert a video', function () {
    $imageGenerator = new Video;

    if (! $imageGenerator->requirementsAreInstalled()) {
        $this->markTestSkipped('Skipping video test because requirements to run it are not met');
    }

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestWebm())->toMediaCollection();

    expect($imageGenerator->canConvert($media))->toBeTrue();

    $imageFile = $imageGenerator->convert($media->getPath(), new Conversion('test'));

    expect(mime_content_type($imageFile))->toEqual('image/jpeg');

    expect(str_replace('.webm', '.jpg', $media->getPath()))->toEqual($imageFile);
});
