<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('returns false for an empty collection', function () {
    $this->assertFalse($this->testModel->hasMedia());
});

it('returns true for a non empty collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertTrue($this->testModel->hasMedia());
});

it('returns true for a non empty collection in an unsaved model', function () {
    $this->testUnsavedModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertTrue($this->testUnsavedModel->hasMedia());
});

it('returns true if any collection is not empty', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    $this->assertTrue($this->testModel->hasMedia('images'));
});

it('returns false for an empty named collection', function () {
    $this->assertFalse($this->testModel->hasMedia('images'));
});

it('returns true for a non empty named collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    $this->assertTrue($this->testModel->hasMedia('images'));
    $this->assertFalse($this->testModel->hasMedia('downloads'));
});

it('returns true for a filtered collection', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->withCustomProperties(['test' => true])
        ->toMediaCollection();

    $this->assertTrue($this->testModel->hasMedia('default'));
    $this->assertTrue($this->testModel->hasMedia('default', ['test' => true]));
    $this->assertFalse($this->testModel->hasMedia('default', ['test' => false]));
});
