<?php

use Programic\MediaLibrary\Conversions\ImageGenerators\Pdf;

it('can convert a pdf', function () {
    $imageGenerator = new Pdf;

    if (! $imageGenerator->requirementsAreInstalled()) {
        $this->markTestSkipped('Skipping pdf test because requirements to run it are not met');
    }

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestPdf())->toMediaCollection();

    expect($imageGenerator->canConvert($media))->toBeTrue();

    $imageFile = $imageGenerator->convert($media->getPath());

    expect(mime_content_type($imageFile))->toEqual('image/jpeg');
});
