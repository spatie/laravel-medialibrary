<?php

use Spatie\MediaLibrary\Conversions\ImageGenerators\Pdf;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can convert a pdf', function () {
    $imageGenerator = new Pdf();

    if (! $imageGenerator->requirementsAreInstalled()) {
        $this->markTestSkipped('Skipping pdf test because requirements to run it are not met');
    }

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestPdf())->toMediaCollection();

    $this->assertTrue($imageGenerator->canConvert($media));

    $imageFile = $imageGenerator->convert($media->getPath());

    $this->assertEquals('image/jpeg', mime_content_type($imageFile));
});
