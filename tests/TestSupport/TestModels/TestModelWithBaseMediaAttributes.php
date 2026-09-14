<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Attributes\MediaConversion;

#[MediaCollection(name: 'banners', singleFile: true, fallbackUrl: '/default.png')]
#[MediaConversion(name: 'thumb', width: 150)]
abstract class TestModelWithBaseMediaAttributes extends TestModel {}
