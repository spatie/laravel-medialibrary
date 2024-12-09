<?php

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\Manipulations;

beforeEach(function () {
    $this->conversionName = 'test';
    $this->conversion = new Conversion($this->conversionName);
});

it('can get its name', function () {
    expect($this->conversion->getName())->toEqual($this->conversionName);
});

it('will add a format parameter if it was not given', function () {
    $this->conversion->width(10);

    expect($this->conversion->getManipulations()->getManipulationArgument('format'))->toEqual(['jpg']);
});

it('will use the format parameter if it was given', function () {
    $this->conversion->format('png');

    expect($this->conversion->getManipulations()->getManipulationArgument('format'))->toEqual(['png']);
});

it('will set conversions to responsive', function () {
    $this->conversion->withResponsiveImages();

    expect($this->conversion->shouldGenerateResponsiveImages())->toBeTrue();
});

it('will set conversions to not be responsive', function () {
    $this->conversion->withResponsiveImages()->withResponsiveImages(false);

    expect($this->conversion->shouldGenerateResponsiveImages())->toBeFalse();
});

it('will be performed on the given collection names', function () {
    $this->conversion->performOnCollections('images', 'downloads');
    expect($this->conversion->shouldBePerformedOn('images'))->toBeTrue();
    expect($this->conversion->shouldBePerformedOn('downloads'))->toBeTrue();
    expect($this->conversion->shouldBePerformedOn('unknown'))->toBeFalse();
});

it('will be performed on all collections if not collection names are set', function () {
    $this->conversion->performOnCollections('*');
    expect($this->conversion->shouldBePerformedOn('images'))->toBeTrue();
    expect($this->conversion->shouldBePerformedOn('downloads'))->toBeTrue();
    expect($this->conversion->shouldBePerformedOn('unknown'))->toBeTrue();
});

it('will be performed on all collections if not collection name is a star', function () {
    expect($this->conversion->shouldBePerformedOn('images'))->toBeTrue();
    expect($this->conversion->shouldBePerformedOn('downloads'))->toBeTrue();
    expect($this->conversion->shouldBePerformedOn('unknown'))->toBeTrue();
});

it('will be queued without config', function () {
    config()->set('media-library.queue_conversions_by_default', null);
    expect($this->conversion->shouldBeQueued())->toBeTrue();
});

it('will be queued by default', function () {
    config()->set('media-library.queue_conversions_by_default', true);
    expect($this->conversion->shouldBeQueued())->toBeTrue();
});

it('will be non queued by default', function () {
    config()->set('media-library.queue_conversions_by_default', false);
    expect($this->conversion->shouldBeQueued())->toBeTrue();
});

it('can be set to queued', function () {
    config()->set('media-library.queue_conversions_by_default', false);
    expect($this->conversion->queued()->shouldBeQueued())->toBeTrue();
});

it('can be set to non queued', function () {
    config()->set('media-library.queue_conversions_by_default', true);
    expect($this->conversion->nonQueued()->shouldBeQueued())->toBeFalse();
});

it('can determine the extension of the result', function () {
    $this->conversion->width(50);

    expect($this->conversion->getResultExtension())->toEqual('jpg');

    $this->conversion->width(100)->format('png');

    expect($this->conversion->getResultExtension())->toEqual('png');
});

it('can remove a previously set manipulation', function () {
    expect($this->conversion->getManipulations()->getManipulationArgument('format'))->toEqual(['jpg']);

    $this->conversion->removeManipulation('format');

    expect($this->conversion->getManipulations()->getManipulationArgument('format'))->toBeNull();
});

it('can remove all previously set manipulations', function () {
    expect($this->conversion->getManipulations()->isEmpty())->toBeFalse();

    $this->conversion->withoutManipulations();

    expect($this->conversion->getManipulations()->isEmpty())->toBeTrue();
});

it('will use the extract duration parameter if it was given', function () {
    $this->conversion->extractVideoFrameAtSecond(10);

    expect($this->conversion->getExtractVideoFrameAtSecond())->toEqual(10);
});

test('manipulations can be set using a closure', function () {
    $this->conversion->setManipulations(function (Manipulations $manipulations) {
        $manipulations->width(10);
    });

    $manipulations = $this->conversion
        ->getManipulations()
        ->toArray();

    $this->assertArrayHasKey('optimize', $manipulations);

    unset($manipulations['optimize']);

    $this->assertEquals([
        'width' => [10],
        'format' => ['jpg'],
    ], $manipulations);
});

it('will optimize the converted image by default', function () {
    $manipulations = (new Conversion('test'))
        ->getManipulations()
        ->getManipulationSequence()
        ->toArray();

    $this->assertArrayHasKey('optimize', $manipulations);
});

it('can remove the optimization', function () {
    $manipulations = (new Conversion('test'))
        ->nonOptimized()
        ->getManipulations()
        ->toArray();

    $this->assertArrayNotHasKey('optimize', $manipulations);
});

it('will use the pdf page number parameter if it was given', function () {
    $this->conversion->pdfPageNumber(10);

    expect($this->conversion->getPdfPageNumber())->toEqual(10);
});
