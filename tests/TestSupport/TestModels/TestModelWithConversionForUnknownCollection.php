<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Attributes\MediaConversion;

#[MediaConversion(name: 'thumb', collections: ['does-not-exist'], width: 150)]
class TestModelWithConversionForUnknownCollection extends TestModel
{
}
