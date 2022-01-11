<?php

it('defaults to jpg when the original file is an image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

    expect($media->getUrl('thumb'))->toHaveExtension('jpg');
});

it('can keep the original image format if the original file is an image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

    expect($media->getUrl('keep_original_format'))->toHaveExtension('png');
});

it('can keep the original image format if the original file is an image with uppercase extension', function () {
    $media = $this->testModelWithConversion->addMedia($this->getUppercaseExtensionTestPng())->toMediaCollection();

    expect($media->getUrl('keep_original_format'))->toHaveExtension('PNG');
});

it('always defaults to jpg when the original file is not an image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestMp4())->toMediaCollection();

    expect($media->getUrl('thumb'))->toHaveExtension('jpg');
    expect($media->getUrl('keep_original_format'))->toHaveExtension('jpg');
});
