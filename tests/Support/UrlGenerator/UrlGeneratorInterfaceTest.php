<?php

use Spatie\MediaLibrary\Support\UrlGenerator\UrlGenerator;

it('declares getPathRelativeToRoot on the url generator interface', function () {
    expect(method_exists(UrlGenerator::class, 'getPathRelativeToRoot'))->toBeTrue();
});
