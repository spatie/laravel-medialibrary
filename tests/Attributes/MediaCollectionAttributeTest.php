<?php

use Spatie\MediaLibrary\Attributes\MediaCollection;

it('stores its configuration as readonly data', function () {
    $attribute = new MediaCollection(
        name: 'avatar',
        singleFile: true,
        onlyKeepLatest: 5,
        acceptsMimeTypes: ['image/jpeg'],
        disk: 'media',
        conversionsDisk: 'media-conversions',
        fallbackUrl: '/default.png',
        fallbackPath: '/var/default.png',
        responsiveImages: true,
    );

    expect($attribute->name)->toBe('avatar')
        ->and($attribute->singleFile)->toBeTrue()
        ->and($attribute->onlyKeepLatest)->toBe(5)
        ->and($attribute->acceptsMimeTypes)->toBe(['image/jpeg'])
        ->and($attribute->disk)->toBe('media')
        ->and($attribute->conversionsDisk)->toBe('media-conversions')
        ->and($attribute->fallbackUrl)->toBe('/default.png')
        ->and($attribute->fallbackPath)->toBe('/var/default.png')
        ->and($attribute->responsiveImages)->toBeTrue();
});

it('defaults optional configuration', function () {
    $attribute = new MediaCollection(name: 'images');

    expect($attribute->singleFile)->toBeFalse()
        ->and($attribute->onlyKeepLatest)->toBeNull()
        ->and($attribute->acceptsMimeTypes)->toBe([])
        ->and($attribute->disk)->toBeNull()
        ->and($attribute->responsiveImages)->toBeFalse();
});
