<?php

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
    expect($this->media->hasCustomProperty('customName'))->toBeTrue();
    expect($this->media->hasCustomProperty('nested.customName'))->toBeTrue();

    expect($this->media->hasCustomProperty('nonExisting'))->toBeFalse();
    expect($this->media->hasCustomProperty('nested.nonExisting'))->toBeFalse();
});

it('can get a custom property', function () {
    expect($this->media->getCustomProperty('customName'))->toEqual('customValue');
    expect($this->media->getCustomProperty('nested.customName'))->toEqual('nested customValue');

    expect($this->media->getCustomProperty('nonExisting'))->toBeNull();
    expect($this->media->getCustomProperty('nested.nonExisting'))->toBeNull();
});

it('can set a custom property', function () {
    $this->media->setCustomProperty('anotherName', 'anotherValue');

    expect($this->media->getCustomProperty('anotherName'))->toEqual('anotherValue');
    expect($this->media->getCustomProperty('customName'))->toEqual('customValue');

    $this->media->setCustomProperty('nested.anotherName', 'anotherValue');
    expect($this->media->getCustomProperty('nested.anotherName'))->toEqual('anotherValue');
});

it('can forget a custom property', function () {
    expect($this->media->hasCustomProperty('customName'))->toBeTrue();
    expect($this->media->hasCustomProperty('nested.customName'))->toBeTrue();

    $this->media->forgetCustomProperty('customName');
    $this->media->forgetCustomProperty('nested.customName');

    expect($this->media->hasCustomProperty('customName'))->toBeFalse();
    expect($this->media->hasCustomProperty('nested.customName'))->toBeFalse();
});

it('returns a fallback if a custom property isnt set', function () {
    expect($this->media->getCustomProperty('imNotHere', 'foo'))->toEqual('foo');
});
