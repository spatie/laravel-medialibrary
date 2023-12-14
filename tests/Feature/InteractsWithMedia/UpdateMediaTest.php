<?php

beforeEach(function () {
    $this->testModel->addMedia($this->getTestJpg())->usingName('test1')->preservingOriginal()->toMediaCollection();
    $this->testModel->addMedia($this->getTestJpg())->usingName('test2')->preservingOriginal()->toMediaCollection();
});

it('removes a media item if its not in the update array', function () {
    $mediaArray = $this->testModel->media->toArray();
    unset($mediaArray[0]);

    $this->testModel->updateMedia($mediaArray);
    $this->testModel->load('media');

    expect($this->testModel->media)->toHaveCount(1);
    expect($this->testModel->getFirstMedia()->name)->toEqual('test2');
});

it('removes a media item with eager loaded relation', function () {
    $mediaArray = $this->testModel->media->toArray();
    unset($mediaArray[0]);

    $this->testModel->load('media');
    $this->testModel->updateMedia($mediaArray);

    expect($this->testModel->media)->toHaveCount(1);
    expect($this->testModel->getFirstMedia()->name)->toEqual('test2');
});

it('renames media items', function () {
    $mediaArray = $this->testModel->media->toArray();

    $mediaArray[0]['name'] = 'testFoo';
    $mediaArray[1]['name'] = 'testBar';

    $this->testModel->updateMedia($mediaArray);
    $this->testModel->load('media');

    expect($this->testModel->media[0]->name)->toEqual('testFoo');
    expect($this->testModel->media[1]->name)->toEqual('testBar');
});

it('updates media item custom properties', function () {
    $mediaArray = $this->testModel->media->toArray();

    $mediaArray[0]['custom_properties']['foo'] = 'bar';

    $this->testModel->updateMedia($mediaArray);
    $this->testModel->load('media');

    expect($this->testModel->media[0]->getCustomProperty('foo'))->toEqual('bar');
});

it('reorders media items', function () {
    $mediaArray = $this->testModel->media->toArray();

    $differentOrder = array_reverse($mediaArray);

    $this->testModel->updateMedia($differentOrder);
    $this->testModel->load('media');

    $orderedMedia = $this->testModel->media->sortBy('order_column');

    expect($orderedMedia[1]->order_column)->toEqual($mediaArray[0]['order_column']);
    expect($orderedMedia[0]->order_column)->toEqual($mediaArray[1]['order_column']);
});
