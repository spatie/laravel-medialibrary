<?php

use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can get a path of an original item', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $expected = $this->makePathOsSafe($this->getMediaDirectory() . "/{$media->id}/test.jpg");

    $actual = $this->makePathOsSafe($media->getPath());

    $this->assertEquals($expected, $actual);
});

it('can get a path of a derived image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionName = 'thumb';

    $expected = $this->makePathOsSafe($this->getMediaDirectory() . "/{$media->id}/conversions/test-{$conversionName}.jpg");

    $actual = $this->makePathOsSafe($media->getPath($conversionName));

    $this->assertEquals($expected, $actual);
});

it('returns an exception when getting a path for an unknown conversion', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->expectException(InvalidConversion::class);

    $media->getPath('unknownConversionName');
});

it('can get a path of an original item with prefix', function () {
    config(['media-library.prefix' => 'prefix']);

    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $expected = $this->makePathOsSafe($this->getMediaDirectory() . "/prefix/{$media->id}/test.jpg");

    $actual = $this->makePathOsSafe($media->getPath());

    $this->assertEquals($expected, $actual);
});

it('can get a path of a derived image with prefix', function () {
    config(['media-library.prefix' => 'prefix']);

    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionName = 'thumb';

    $expected = $this->makePathOsSafe($this->getMediaDirectory() . "/prefix/{$media->id}/conversions/test-{$conversionName}.jpg");

    $actual = $this->makePathOsSafe($media->getPath($conversionName));

    $this->assertEquals($expected, $actual);
});
