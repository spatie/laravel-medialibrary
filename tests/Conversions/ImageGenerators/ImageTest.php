<?php

use Programic\MediaLibrary\Conversions\ImageGenerators\Image;

it('can convert an image', function () {
    $imageGenerator = new Image;

    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection();

    expect($imageGenerator->canConvert($media))->toBeTrue();

    $imageFile = $imageGenerator->convert($media->getPath());

    expect(mime_content_type($imageFile))->toEqual('image/jpeg');
    expect($media->getPath())->toEqual($imageFile);
});

it(
    'can convert a tiff image',
    function () {
        // TIFF format requires imagick
        config(['media-library.image_driver' => 'imagick']);

        $imageGenerator = new Image;

        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestTiff())->toMediaCollection();

        expect($imageGenerator->canConvert($media))->toBeTrue();

        $imageFile = $imageGenerator->convert($media->getPath());

        expect(mime_content_type($imageFile))->toEqual('image/tiff');
        expect($media->getPath())->toEqual($imageFile);
    }
)->skip(! extension_loaded('imagick'), 'The imagick extension is not available.');

it(
    'can convert a heic image',
    function () {
        // heic format requires imagick
        config(['media-library.image_driver' => 'imagick']);

        $imageGenerator = new Image;

        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestHeic())->toMediaCollection();

        expect($imageGenerator->canConvert($media))->toBeTrue();

        $imageFile = $imageGenerator->convert($media->getPath());

        expect(mime_content_type($imageFile))->toEqual('image/heic');
        expect($media->getPath())->toEqual($imageFile);
    }
)->skip(! extension_loaded('imagick'), 'The imagick extension is not available.');
