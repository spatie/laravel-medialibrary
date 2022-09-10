<?php

use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;

it('can get a path of an original item relative to the filesystem\'s root', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $expected = $this->makePathOsSafe("{$media->id}/test.jpg");

    $actual = $this->makePathOsSafe($media->getPathRelativeToRoot());

    expect($actual)->toEqual($expected);
});

it('can get a path of a derived image relative to the filesystem\'s root', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionName = 'thumb';

    $expected = $this->makePathOsSafe("{$media->id}/conversions/test-{$conversionName}.jpg");

    $actual = $this->makePathOsSafe($media->getPathRelativeToRoot($conversionName));

    expect($actual)->toEqual($expected);
});

it('returns an exception when getting a relative path for an unknown conversion', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->expectException(InvalidConversion::class);

    $media->getPathRelativeToRoot('unknownConversionName');
});

it('can get a path of an original item with prefix relative to the filesystem\'s root', function () {
    config(['media-library.prefix' => 'prefix']);

    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $expected = $this->makePathOsSafe("prefix/{$media->id}/test.jpg");

    $actual = $this->makePathOsSafe($media->getPathRelativeToRoot());

    expect($actual)->toEqual($expected);
});

it('can get a path of a derived image with prefix relative to the filesystem\'s root', function () {
    config(['media-library.prefix' => 'prefix']);

    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionName = 'thumb';

    $expected = $this->makePathOsSafe("prefix/{$media->id}/conversions/test-{$conversionName}.jpg");

    $actual = $this->makePathOsSafe($media->getPathRelativeToRoot($conversionName));

    expect($actual)->toEqual($expected);
});
