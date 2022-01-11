<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can get the names of registered conversions', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    $this->assertSame([], $media->getMediaConversionNames());

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    $this->assertSame(['thumb', 'keep_original_format'], $media->getMediaConversionNames());
});
