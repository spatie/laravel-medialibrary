<?php

use Spatie\MediaLibrary\Support\ImageFactory;

test('loading an image uses the correct driver', function () {
    config(['media-library.image_driver' => 'imagick']);

    $image = ImageFactory::load($this->getTestJpg());

    $reflection = new ReflectionClass($image);

    $imageDriver = $reflection->getProperty('imageDriver');

    $imageDriver->setAccessible(true);

    $imageDriverValue = $imageDriver->getValue($image);

    expect($imageDriverValue)->toEqual('imagick');
});
