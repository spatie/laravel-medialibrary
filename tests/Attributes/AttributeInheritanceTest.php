<?php

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\MediaAttributes\MediaAttributeResolver;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithInheritedMediaAttributes;

beforeEach(function () {
    MediaAttributeResolver::clearCache();

    $this->model = new TestModelWithInheritedMediaAttributes;
});

afterEach(fn () => MediaAttributeResolver::clearCache());

it('picks up a collection declared on a parent class', function () {
    $collection = $this->model->getMediaCollection('banners');

    expect($collection)->not->toBeNull()
        ->and($collection->singleFile)->toBeTrue()
        ->and($this->model->getFallbackMediaUrl('banners'))->toBe('/default.png');
});

it('lets a child redeclare a conversion its parent declared', function () {
    $this->model->registerAllMediaConversions();

    $thumbs = collect($this->model->mediaConversions)
        ->filter(fn (Conversion $conversion) => $conversion->getName() === 'thumb');

    expect($thumbs)->toHaveCount(1)
        ->and($thumbs->first()->getManipulations()->getFirstManipulationArgument('width'))->toBe(400);
});
