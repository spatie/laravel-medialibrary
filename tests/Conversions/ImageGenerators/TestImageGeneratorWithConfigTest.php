<?php

use Programic\MediaLibrary\Conversions\ImageGenerators\ImageGeneratorFactory;
use Programic\MediaLibrary\Tests\TestSupport\TestImageGeneratorWithConfig;

test('image generators can get parameter from the config file', function () {
    config()->set('media-library.image_generators', [
        TestImageGeneratorWithConfig::class => ['firstName' => 'firstValue', 'secondName' => 'secondValue'],
    ]);

    $imageGenerators = ImageGeneratorFactory::getImageGenerators();

    $testGeneratorWithConfig = $imageGenerators->first();

    expect($testGeneratorWithConfig)->toBeInstanceOf(TestImageGeneratorWithConfig::class);

    expect($testGeneratorWithConfig->firstName)->toEqual('firstValue');
    expect($testGeneratorWithConfig->secondName)->toEqual('secondValue');
});

test('image generators will receive config parameters by name', function () {
    config()->set('media-library.image_generators', [
        TestImageGeneratorWithConfig::class => ['secondName' => 'secondValue', 'firstName' => 'firstValue'],
    ]);

    $imageGenerators = ImageGeneratorFactory::getImageGenerators();

    $testGeneratorWithConfig = $imageGenerators->first();

    expect($testGeneratorWithConfig)->toBeInstanceOf(TestImageGeneratorWithConfig::class);
    expect($testGeneratorWithConfig->firstName)->toEqual('firstValue');
    expect($testGeneratorWithConfig->secondName)->toEqual('secondValue');
});
