<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestImageGenerator;

it('can convert an image with a valid extension and mime type', function () {
    $generator = new TestImageGenerator;
    $generator->shouldMatchBothExtensionsAndMimeTypes = true;

    $generator->supportedMimetypes->push('supported-mime-type');
    $generator->supportedExtensions->push('supported-extension');

    $media = new Media;
    $media->mime_type = 'supported-mime-type';
    $media->file_name = 'some-file.supported-extension';

    expect($generator->canConvert($media))->toBeTrue();
});

it('cannot convert an image with an invalid extension and mime type', function () {
    $generator = new TestImageGenerator;
    $generator->shouldMatchBothExtensionsAndMimeTypes = true;

    $generator->supportedMimetypes->push('supported-mime-type');
    $generator->supportedExtensions->push('supported-extension');

    $media = new Media;
    $media->mime_type = 'invalid-mime-type';
    $media->file_name = 'some-file.invalid-extension';

    expect($generator->canConvert($media))->toBeFalse();
});

it('cannot convert an image with only a valid mime type', function () {
    $generator = new TestImageGenerator;
    $generator->shouldMatchBothExtensionsAndMimeTypes = true;

    $generator->supportedMimetypes->push('supported-mime-type');
    $generator->supportedExtensions->push('supported-extension');

    $media = new Media;
    $media->mime_type = 'supported-mime-type';
    $media->file_name = 'some-file.invalid-extension';

    expect($generator->canConvert($media))->toBeFalse();
});

it('cannot convert an image with only a valid extension', function () {
    $generator = new TestImageGenerator;
    $generator->shouldMatchBothExtensionsAndMimeTypes = true;

    $generator->supportedExtensions->push('supported-extension');
    $generator->supportedMimetypes->push('supported-mime-type');

    $media = new Media;
    $media->mime_type = 'invalid-mime-type';
    $media->file_name = 'some-file.supported-extension';

    expect($generator->canConvert($media))->toBeFalse();
});
