<?php

use Spatie\MediaLibrary\Conversions\ImageGenerators\Webp;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can convert a webp', function () {
    $imageGenerator = new Webp();

    if (! $imageGenerator->requirementsAreInstalled()) {
        $this->markTestSkipped('Skipping webp test because requirements to run it are not met');
    }

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestWebp())->toMediaCollection();

    $this->assertTrue($imageGenerator->canConvert($media));

    $imageFile = $imageGenerator->convert($media->getPath());

    $this->assertEquals('image/png', mime_content_type($imageFile));
});
