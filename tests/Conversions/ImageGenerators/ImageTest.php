<?php

use Spatie\MediaLibrary\Conversions\ImageGenerators\Image;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can convert an image', function () {
    $imageGenerator = new Image();

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertTrue($imageGenerator->canConvert($media));

    $imageFile = $imageGenerator->convert($media->getPath());

    $this->assertEquals('image/jpeg', mime_content_type($imageFile));
    $this->assertEquals($imageFile, $media->getPath());
});
