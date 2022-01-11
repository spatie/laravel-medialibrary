<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties([
            'customName' => 'customValue',
            'nested' => [
                'customName' => 'nested customValue',
            ],
        ])
        ->toMediaCollection('images');
});

it('can determine if a media item has a custom property', function () {
    $this->assertTrue($this->media->hasCustomProperty('customName'));
    $this->assertTrue($this->media->hasCustomProperty('nested.customName'));

    $this->assertFalse($this->media->hasCustomProperty('nonExisting'));
    $this->assertFalse($this->media->hasCustomProperty('nested.nonExisting'));
});

it('can get a custom property', function () {
    $this->assertEquals('customValue', $this->media->getCustomProperty('customName'));
    $this->assertEquals('nested customValue', $this->media->getCustomProperty('nested.customName'));

    $this->assertNull($this->media->getCustomProperty('nonExisting'));
    $this->assertNull($this->media->getCustomProperty('nested.nonExisting'));
});

it('can set a custom property', function () {
    $this->media->setCustomProperty('anotherName', 'anotherValue');

    $this->assertEquals('anotherValue', $this->media->getCustomProperty('anotherName'));
    $this->assertEquals('customValue', $this->media->getCustomProperty('customName'));

    $this->media->setCustomProperty('nested.anotherName', 'anotherValue');
    $this->assertEquals('anotherValue', $this->media->getCustomProperty('nested.anotherName'));
});

it('can forget a custom property', function () {
    $this->assertTrue($this->media->hasCustomProperty('customName'));
    $this->assertTrue($this->media->hasCustomProperty('nested.customName'));

    $this->media->forgetCustomProperty('customName');
    $this->media->forgetCustomProperty('nested.customName');

    $this->assertFalse($this->media->hasCustomProperty('customName'));
    $this->assertFalse($this->media->hasCustomProperty('nested.customName'));
});

it('returns a fallback if a custom property isnt set', function () {
    $this->assertEquals('foo', $this->media->getCustomProperty('imNotHere', 'foo'));
});
