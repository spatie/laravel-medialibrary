<?php

use Spatie\MediaLibrary\Conversions\ImageGenerators\Svg;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can convert a svg', function () {
    $imageGenerator = new Svg();

    if (! $imageGenerator->requirementsAreInstalled()) {
        $this->markTestSkipped('Skipping svg test because requirements to run it are not met');
    }

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestSvg())->toMediaCollection();

    $this->assertTrue($imageGenerator->canConvert($media));

    $imageFile = $imageGenerator->convert($media->getPath());

    $this->assertEquals('image/jpeg', mime_content_type($imageFile));
});
