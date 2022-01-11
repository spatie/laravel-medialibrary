<?php

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->conversion = new Conversion($this->conversionName);
});

it('can get its name', function () {
    $this->assertEquals($this->conversionName, $this->conversion->getName());
});

it('will add a format parameter if it was not given', function () {
    $this->conversion->width(10);

    $this->assertEquals('jpg', $this->conversion->getManipulations()->getManipulationArgument('format'));
});

it('will use the format parameter if it was given', function () {
    $this->conversion->format('png');

    $this->assertEquals('png', $this->conversion->getManipulations()->getManipulationArgument('format'));
});

it('will be performed on the given collection names', function () {
    $this->conversion->performOnCollections('images', 'downloads');
    $this->assertTrue($this->conversion->shouldBePerformedOn('images'));
    $this->assertTrue($this->conversion->shouldBePerformedOn('downloads'));
    $this->assertFalse($this->conversion->shouldBePerformedOn('unknown'));
});

it('will be performed on all collections if not collection names are set', function () {
    $this->conversion->performOnCollections('*');
    $this->assertTrue($this->conversion->shouldBePerformedOn('images'));
    $this->assertTrue($this->conversion->shouldBePerformedOn('downloads'));
    $this->assertTrue($this->conversion->shouldBePerformedOn('unknown'));
});

it('will be performed on all collections if not collection name is a star', function () {
    $this->assertTrue($this->conversion->shouldBePerformedOn('images'));
    $this->assertTrue($this->conversion->shouldBePerformedOn('downloads'));
    $this->assertTrue($this->conversion->shouldBePerformedOn('unknown'));
});

it('will be queued without config', function () {
    config()->set('media-library.queue_conversions_by_default', null);
    $this->assertTrue($this->conversion->shouldBeQueued());
});

it('will be queued by default', function () {
    config()->set('media-library.queue_conversions_by_default', true);
    $this->assertTrue($this->conversion->shouldBeQueued());
});

it('will be non queued by default', function () {
    config()->set('media-library.queue_conversions_by_default', false);
    $this->assertTrue($this->conversion->shouldBeQueued());
});

it('can be set to queued', function () {
    config()->set('media-library.queue_conversions_by_default', false);
    $this->assertTrue($this->conversion->queued()->shouldBeQueued());
});

it('can be set to non queued', function () {
    config()->set('media-library.queue_conversions_by_default', true);
    $this->assertFalse($this->conversion->nonQueued()->shouldBeQueued());
});

it('can determine the extension of the result', function () {
    $this->conversion->width(50);

    $this->assertEquals('jpg', $this->conversion->getResultExtension());

    $this->conversion->width(100)->format('png');

    $this->assertEquals('png', $this->conversion->getResultExtension());
});

it('can remove a previously set manipulation', function () {
    $this->assertEquals('jpg', $this->conversion->getManipulations()->getManipulationArgument('format'));

    $this->conversion->removeManipulation('format');

    $this->assertNull($this->conversion->getManipulations()->getManipulationArgument('format'));
});

it('can remove all previously set manipulations', function () {
    $this->assertFalse($this->conversion->getManipulations()->isEmpty());

    $this->conversion->withoutManipulations();

    $this->assertTrue($this->conversion->getManipulations()->isEmpty());
});

it('will use the extract duration parameter if it was given', function () {
    $this->conversion->extractVideoFrameAtSecond(10);

    $this->assertEquals(10, $this->conversion->getExtractVideoFrameAtSecond());
});

test('manipulations can be set using an instance of manipulations', function () {
    $this->conversion->setManipulations((new Manipulations())->width(10));

    $manipulations = $this->conversion
        ->getManipulations()
        ->getManipulationSequence()
        ->toArray();

    $this->assertArrayHasKey('optimize', $manipulations[0]);

    unset($manipulations[0]['optimize']);

    $this->assertEquals([[
        'width' => 10,
        'format' => 'jpg',
    ]], $manipulations);
});

test('manipulations can be set using a closure', function () {
    $this->conversion->setManipulations(function (Manipulations $manipulations) {
        $manipulations->width(10);
    });

    $manipulations = $this->conversion
        ->getManipulations()
        ->getManipulationSequence()
        ->toArray();

    $this->assertArrayHasKey('optimize', $manipulations[0]);

    unset($manipulations[0]['optimize']);

    $this->assertEquals([[
        'width' => 10,
        'format' => 'jpg',
    ]], $manipulations);
});

it('will optimize the converted image by default', function () {
    $manipulations = (new Conversion('test'))
        ->getManipulations()
        ->getManipulationSequence()
        ->toArray();

    $this->assertArrayHasKey('optimize', $manipulations[0]);
});

it('can remove the optimization', function () {
    $manipulations = (new Conversion('test'))
        ->nonOptimized()
        ->getManipulations()
        ->getManipulationSequence()
        ->toArray();

    $this->assertArrayNotHasKey('optimize', $manipulations[0]);
});

it('will use the pdf page number parameter if it was given', function () {
    $this->conversion->pdfPageNumber(10);

    $this->assertEquals(10, $this->conversion->getPdfPageNumber());
});
