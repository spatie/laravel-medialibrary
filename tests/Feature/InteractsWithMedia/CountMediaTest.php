<?php

it('returns zero for an empty collection', function () {
    expect($this->testModel->countMedia())->toEqual(0);
});

it('returns count for a non empty collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();
    $this->testModel->addMedia($this->getTestPng())->toMediaCollection();

    expect($this->testModel->countMedia())->toEqual(2);
});

it('returns count for a non empty collection in an unsaved model', function () {
    $this->testUnsavedModel->addMedia($this->getTestJpg())->toMediaCollection();

    expect($this->testUnsavedModel->countMedia())->toEqual(1);
});

it('returns count if any collection is not empty', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect($this->testModel->countMedia('images'))->toEqual(1);
});

it('returns zero for an empty named collection', function () {
    expect($this->testModel->countMedia('images'))->toEqual(0);
});

it('returns count for a non empty named collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect($this->testModel->countMedia('images'))->toEqual(1);
    expect($this->testModel->countMedia('downloads'))->toEqual(0);
});

it('returns count for a filtered collection', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->withCustomProperties(['test' => true])
        ->toMediaCollection();

    expect($this->testModel->countMedia('default'))->toEqual(1);
    expect($this->testModel->countMedia('default', ['test' => true]))->toEqual(1);
    expect($this->testModel->countMedia('default', ['test' => false]))->toEqual(0);
});
