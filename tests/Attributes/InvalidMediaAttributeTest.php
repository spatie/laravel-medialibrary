<?php

use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidMediaAttribute;

it('builds a duplicate collection message', function () {
    $exception = InvalidMediaAttribute::duplicateCollection('avatar', 'App\\Models\\User');

    expect($exception)->toBeInstanceOf(InvalidMediaAttribute::class)
        ->and($exception->getMessage())->toContain('avatar')
        ->and($exception->getMessage())->toContain('App\\Models\\User');
});

it('builds an unknown collection message', function () {
    $exception = InvalidMediaAttribute::unknownCollection('thumb', 'missing', 'App\\Models\\User');

    expect($exception->getMessage())->toContain('thumb')
        ->and($exception->getMessage())->toContain('missing');
});
