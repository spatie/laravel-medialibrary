<?php

use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithClosureConversion;

beforeEach(function () {
    $model = TestModelWithClosureConversion::create(['name' => 'test']);

    $this->media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
});

it('names the conversion file after the format it is actually written in', function () {
    $path = $this->media->getPath('closure-thumb');

    expect($path)->toEndWith('.webp')
        ->and(mime_content_type($path))->toBe('image/webp');
});

it('lets the closure refine the manipulations of the conversion', function () {
    [$width] = getimagesize($this->media->getPath('closure-thumb'));

    expect($width)->toBe(60);
});
