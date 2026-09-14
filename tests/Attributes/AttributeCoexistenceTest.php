<?php

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\MediaAttributes\MediaAttributeResolver;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithAttributesAndMethods;

beforeEach(fn () => MediaAttributeResolver::clearCache());

it('merges attribute-defined and method-defined collections and conversions', function () {
    $model = new TestModelWithAttributesAndMethods;

    $collectionNames = $model->getRegisteredMediaCollections()->pluck('name')->all();

    expect($collectionNames)->toContain('images', 'documents');

    $model->registerAllMediaConversions();

    $conversionNames = collect($model->mediaConversions)
        ->map(fn (Conversion $conversion) => $conversion->getName())
        ->all();

    expect($conversionNames)->toContain('thumb', 'large');
});
