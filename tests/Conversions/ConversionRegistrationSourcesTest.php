<?php

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversionInCollections;

it('keeps conversions registered from within registerMediaCollections', function () {
    $model = TestModelWithConversionInCollections::create(['name' => 'test']);

    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $names = ConversionCollection::createForMedia($media)
        ->map(fn (Conversion $conversion) => $conversion->getName())
        ->all();

    expect($names)->toContain('legacy-thumb');
});

it('registers each conversion once no matter how often registration runs', function () {
    $model = TestModelWithConversionInCollections::create(['name' => 'test']);

    $model->registerAllMediaConversions();
    $model->registerAllMediaConversions();

    $names = collect($model->mediaConversions)->map(fn (Conversion $conversion) => $conversion->getName());

    expect($names->filter(fn (string $name) => $name === 'legacy-thumb'))->toHaveCount(1);
});
