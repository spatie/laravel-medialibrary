<?php

use Spatie\MediaLibrary\Support\ImageFactory;

test('loading an image uses the correct driver', function () {
    config(['medialibrary.image_driver' => 'imagick']);

    $image = ImageFactory::load($this->getTestJpg());

    expect($image->driverName())->toBe('imagick');
});
