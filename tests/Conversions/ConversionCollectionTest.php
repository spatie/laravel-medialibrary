<?php

use Spatie\MediaLibrary\Conversions\ConversionCollection;

beforeEach(function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    $media->manipulations = ['thumb' => ['filter' => 'greyscale', 'height' => 10]];
    $media->save();

    $secondMedia = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    $secondMedia->manipulations = ['thumb' => ['filter' => 'greyscale', 'height' => 20]];
    $secondMedia->save();

    $this->media = $media->fresh();
    $this->secondMedia = $media->fresh();
});

it('will prepend the manipulation saved on the model and the wildmark manipulations', function () {
    $this->media->manipulations = [
        '*' => ['brightness' => '-80'],
        'thumb' => ['filter' => 'greyscale', 'height' => 10],
    ];

    $conversionCollection = ConversionCollection::createForMedia($this->media);

    $conversion = $conversionCollection->getConversions()[0];

    expect($conversion->getName())->toEqual('thumb');

    $manipulationSequence = $conversion
        ->getManipulations()
        ->getManipulationSequence()
        ->toArray();

    $this->assertArrayHasKey('optimize', $manipulationSequence[0]);

    unset($manipulationSequence[0]['optimize']);

    $this->assertEquals([[
        'brightness' => '-80',
        'filter' => 'greyscale',
        'height' => 10,
        'width' => 50,
        'format' => 'jpg',
    ]], $manipulationSequence);
});

it('will prepend the manipulation saved on the model', function () {
    $conversionCollection = ConversionCollection::createForMedia($this->media);

    $conversion = $conversionCollection->getConversions()[0];

    expect($conversion->getName())->toEqual('thumb');

    $manipulationSequence = $conversion
        ->getManipulations()
        ->getManipulationSequence()
        ->toArray();

    $this->assertArrayHasKey('optimize', $manipulationSequence[0]);

    unset($manipulationSequence[0]['optimize']);

    $this->assertEquals([[
        'filter' => 'greyscale',
        'height' => 10,
        'width' => 50,
        'format' => 'jpg',
    ]], $manipulationSequence);
});

it('will apply the manipulation on the equally named conversion of every model', function () {
    $mediaItems = [$this->media, $this->secondMedia];
    $manipulations = [];

    foreach ($mediaItems as $mediaItem) {
        $conversionCollection = ConversionCollection::createForMedia($mediaItem);

        $conversion = $conversionCollection->getConversions()[0];

        $manipulationSequence = $conversion
            ->getManipulations()
            ->getManipulationSequence()
            ->toArray();

        $manipulations[] = $manipulationSequence;
    }

    expect($manipulations[1])->toEqual($manipulations[0]);
});
