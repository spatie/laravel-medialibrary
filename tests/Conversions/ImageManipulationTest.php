<?php

use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\BorderType;
use Spatie\Image\Enums\Constraint;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\FlipDirection;
use Spatie\Image\Image;
use Programic\MediaLibrary\Conversions\Manipulations;

it('transforms parameters correctly', function () {
    // Mock the image object
    $image = Image::load(pathToImage: $this->getTestJpg());

    // Define manipulations
    $manipulations = [
        'border' => ['width' => 10, 'type' => 'expand'],
        'watermark' => ['fit' => 'contain', 'watermarkImage' => $this->getTestPng()],
        'resizeCanvas' => ['position' => 'center'],
        'resize' => ['constraints' => ['preserveAspectRatio'], 'width' => 100, 'height' => 100],
        'crop' => ['width' => 50, 'height' => 50, 'position' => 'topLeft'],
        'fit' => ['fit' => 'contain'],
        'flip' => ['flip' => 'horizontal'],
    ];

    $transformedParameters = [];

    // Create an instance of the class containing the logic
    $manipulator = new Manipulations($manipulations);

    foreach ($manipulations as $manipulationName => $parameters) {
        $parameters = $manipulator->transformParameters($manipulationName, $parameters);

        // Apply the manipulation
        $image->$manipulationName(...$parameters);

        // Store the transformed parameters for assertions
        $transformedParameters[$manipulationName] = $parameters;
    }

    // Assertions to check if parameters have been correctly transformed
    expect($transformedParameters['border']['type'])->toBeInstanceOf(BorderType::class)
        ->and($transformedParameters['watermark']['fit'])->toBeInstanceOf(Fit::class)
        ->and($transformedParameters['resizeCanvas']['position'])->toBeInstanceOf(AlignPosition::class)
        ->and($transformedParameters['resize']['constraints'][0])->toBeInstanceOf(Constraint::class)
        ->and($transformedParameters['crop']['position'])->toBeInstanceOf(CropPosition::class)
        ->and($transformedParameters['fit']['fit'])->toBeInstanceOf(Fit::class)
        ->and($transformedParameters['flip']['flip'])->toBeInstanceOf(FlipDirection::class);
});

it('handles parameters that are already enum instances', function () {
    // Mock the image object
    $image = Image::load(pathToImage: $this->getTestJpg());

    // Define manipulations with parameters already as enum instances
    $manipulations = [
        'border' => ['width' => 10, 'type' => BorderType::Expand],
        'watermark' => ['fit' => Fit::Contain, 'watermarkImage' => $this->getTestPng()],
        'resizeCanvas' => ['position' => AlignPosition::Center],
        'resize' => ['constraints' => [Constraint::PreserveAspectRatio], 'width' => 100, 'height' => 100],
        'crop' => ['width' => 50, 'height' => 50, 'position' => CropPosition::TopLeft],
        'fit' => ['fit' => Fit::Contain],
        'flip' => ['flip' => FlipDirection::Horizontal],
    ];

    $transformedParameters = [];

    // Create an instance of the class containing the logic
    $manipulator = new Manipulations($manipulations);

    foreach ($manipulations as $manipulationName => $parameters) {
        $parameters = $manipulator->transformParameters($manipulationName, $parameters);

        // Apply the manipulation
        $image->$manipulationName(...$parameters);

        // Store the transformed parameters for assertions
        $transformedParameters[$manipulationName] = $parameters;
    }

    // Assertions to check if parameters remain unchanged
    expect($transformedParameters['border']['type'])->toBeInstanceOf(BorderType::class)
        ->and($transformedParameters['watermark']['fit'])->toBeInstanceOf(Fit::class)
        ->and($transformedParameters['resizeCanvas']['position'])->toBeInstanceOf(AlignPosition::class)
        ->and($transformedParameters['resize']['constraints'][0])->toBeInstanceOf(Constraint::class)
        ->and($transformedParameters['crop']['position'])->toBeInstanceOf(CropPosition::class)
        ->and($transformedParameters['fit']['fit'])->toBeInstanceOf(Fit::class)
        ->and($transformedParameters['flip']['flip'])->toBeInstanceOf(FlipDirection::class);
});
