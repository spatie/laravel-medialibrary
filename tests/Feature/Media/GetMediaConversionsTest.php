<?php

it('can get the names of registered conversions', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    expect($media->getMediaConversionNames())->toBe([]);

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    expect($media->getMediaConversionNames())->toBe(['thumb', 'keep_original_format']);
});
