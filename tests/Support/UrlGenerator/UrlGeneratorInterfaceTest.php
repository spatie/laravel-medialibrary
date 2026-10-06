<?php

use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\Support\UrlGenerator\UrlGenerator;
use Spatie\MediaLibrary\Tests\TestSupport\TestInterfaceUrlGenerator;

beforeEach(function () {
    config()->set('media-library.url_generator', TestInterfaceUrlGenerator::class);
});

it('declares getPathRelativeToRoot on the url generator interface', function () {
    expect(method_exists(UrlGenerator::class, 'getPathRelativeToRoot'))->toBeTrue();
});

it('can get the path relative to root with a url generator that only implements the interface', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    expect($media->getPathRelativeToRoot())->toEqual($media->id.'/test.jpg');
    expect($media->getPathRelativeToRoot('thumb'))->toEqual($media->id.'/conversions/test-thumb.jpg');
});

it('can delete media with a url generator that only implements the interface', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeTrue();

    $media->delete();

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeFalse();
});
