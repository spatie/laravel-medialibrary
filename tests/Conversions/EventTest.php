<?php

use Illuminate\Support\Facades\Event;
use Programic\MediaLibrary\Conversions\Events\ConversionHasBeenCompleted;
use Programic\MediaLibrary\Conversions\Events\ConversionWillStart;
use Programic\MediaLibrary\MediaCollections\Events\CollectionHasBeenCleared;

beforeEach(function () {
    Event::fake();
});

it('will fire the conversion will start event', function () {
    $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection('images');

    Event::assertDispatched(ConversionWillStart::class);
});

it('will fire the conversion complete event', function () {
    $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection('images');

    Event::assertDispatched(ConversionHasBeenCompleted::class);
});

it('will fire the collection cleared event', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('images');

    $this->testModel->clearMediaCollection('images');

    Event::assertDispatched(CollectionHasBeenCleared::class);
});
