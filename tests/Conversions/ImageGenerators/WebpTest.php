<?php

use Programic\MediaLibrary\Conversions\ImageGenerators\Webp;

it('can convert a webp', function () {
    $imageGenerator = new Webp;

    if (! $imageGenerator->requirementsAreInstalled()) {
        $this->markTestSkipped('Skipping webp test because requirements to run it are not met');
    }

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestWebp())->toMediaCollection();

    expect($imageGenerator->canConvert($media))->toBeTrue();

    $imageFile = $imageGenerator->convert($media->getPath());

    expect(mime_content_type($imageFile))->toEqual('image/png');
});
