<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->testModel->addMedia($this->getTestJpg())->usingName('test1')->preservingOriginal()->toMediaCollection();
    $this->testModel->addMedia($this->getTestJpg())->usingName('test2')->preservingOriginal()->toMediaCollection();
});

it('removes a media item if its not in the update array', function () {
    $mediaArray = $this->testModel->media->toArray();
    unset($mediaArray[0]);

    $this->testModel->updateMedia($mediaArray);
    $this->testModel->load('media');

    $this->assertCount(1, $this->testModel->media);
    $this->assertEquals('test2', $this->testModel->getFirstMedia()->name);
});

it('removes a media item with eager loaded relation', function () {
    $mediaArray = $this->testModel->media->toArray();
    unset($mediaArray[0]);

    $this->testModel->load('media');
    $this->testModel->updateMedia($mediaArray);

    $this->assertCount(1, $this->testModel->media);
    $this->assertEquals('test2', $this->testModel->getFirstMedia()->name);
});

it('renames media items', function () {
    $mediaArray = $this->testModel->media->toArray();

    $mediaArray[0]['name'] = 'testFoo';
    $mediaArray[1]['name'] = 'testBar';

    $this->testModel->updateMedia($mediaArray);
    $this->testModel->load('media');

    $this->assertEquals('testFoo', $this->testModel->media[0]->name);
    $this->assertEquals('testBar', $this->testModel->media[1]->name);
});

it('updates media item custom properties', function () {
    $mediaArray = $this->testModel->media->toArray();

    $mediaArray[0]['custom_properties']['foo'] = 'bar';

    $this->testModel->updateMedia($mediaArray);
    $this->testModel->load('media');

    $this->assertEquals('bar', $this->testModel->media[0]->getCustomProperty('foo'));
});

it('reorders media items', function () {
    $mediaArray = $this->testModel->media->toArray();

    $differentOrder = array_reverse($mediaArray);

    $this->testModel->updateMedia($differentOrder);
    $this->testModel->load('media');

    $orderedMedia = $this->testModel->media->sortBy('order_column');

    $this->assertEquals($mediaArray[0]['order_column'], $orderedMedia[1]->order_column);
    $this->assertEquals($mediaArray[1]['order_column'], $orderedMedia[0]->order_column);
});
