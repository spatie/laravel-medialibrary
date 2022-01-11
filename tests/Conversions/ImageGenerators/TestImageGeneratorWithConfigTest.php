<?php

use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGeneratorFactory;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestImageGeneratorWithConfig;

uses(TestCase::class);

test('image generators can get parameter from the config file', function () {
    config()->set('media-library.image_generators', [
        TestImageGeneratorWithConfig::class => ['firstName' => 'firstValue', 'secondName' => 'secondValue'],
    ]);

    $imageGenerators = ImageGeneratorFactory::getImageGenerators();

    $testGeneratorWithConfig = $imageGenerators->first();

    $this->assertInstanceOf(TestImageGeneratorWithConfig::class, $testGeneratorWithConfig);

    $this->assertEquals('firstValue', $testGeneratorWithConfig->firstName);
    $this->assertEquals('secondValue', $testGeneratorWithConfig->secondName);
});

test('image generators will receive config parameters by name', function () {
    config()->set('media-library.image_generators', [
        TestImageGeneratorWithConfig::class => ['secondName' => 'secondValue', 'firstName' => 'firstValue', ],
    ]);

    $imageGenerators = ImageGeneratorFactory::getImageGenerators();

    $testGeneratorWithConfig = $imageGenerators->first();

    $this->assertInstanceOf(TestImageGeneratorWithConfig::class, $testGeneratorWithConfig);
    $this->assertEquals('firstValue', $testGeneratorWithConfig->firstName);
    $this->assertEquals('secondValue', $testGeneratorWithConfig->secondName);
});
