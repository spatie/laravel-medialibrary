<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

test('test', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
});
