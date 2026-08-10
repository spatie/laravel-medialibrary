<?php

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Attributes\MediaConversion;

it('stores its configuration as readonly data', function () {
    $attribute = new MediaConversion(
        name: 'thumb',
        collections: ['avatar'],
        width: 150,
        height: 100,
        fit: Fit::Crop,
        format: 'webp',
        quality: 80,
        queued: false,
        responsiveImages: true,
        keepOriginalImageFormat: true,
    );

    expect($attribute->name)->toBe('thumb')
        ->and($attribute->collections)->toBe(['avatar'])
        ->and($attribute->width)->toBe(150)
        ->and($attribute->height)->toBe(100)
        ->and($attribute->fit)->toBe(Fit::Crop)
        ->and($attribute->format)->toBe('webp')
        ->and($attribute->quality)->toBe(80)
        ->and($attribute->queued)->toBeFalse()
        ->and($attribute->responsiveImages)->toBeTrue()
        ->and($attribute->keepOriginalImageFormat)->toBeTrue();
});

it('defaults optional configuration', function () {
    $attribute = new MediaConversion(name: 'thumb');

    expect($attribute->collections)->toBe([])
        ->and($attribute->width)->toBeNull()
        ->and($attribute->height)->toBeNull()
        ->and($attribute->fit)->toBeNull()
        ->and($attribute->format)->toBeNull()
        ->and($attribute->quality)->toBeNull()
        ->and($attribute->queued)->toBeNull()
        ->and($attribute->responsiveImages)->toBeFalse()
        ->and($attribute->keepOriginalImageFormat)->toBeFalse();
});
