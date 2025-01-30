<?php

use Programic\MediaLibrary\Conversions\ImageGenerators\Avif;

it('can convert a avif', function () {
    $imageGenerator = new Avif;

    if (! $imageGenerator->requirementsAreInstalled()) {
        $this->markTestSkipped('Skipping avif test because requirements to run it are not met');
    }

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestAvif())->toMediaCollection();

    expect($imageGenerator->canConvert($media))->toBeTrue();

    $imageFile = $imageGenerator->convert($media->getPath());

    expect(mime_content_type($imageFile))->toEqual('image/png');
});
