<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Attributes\MediaConversion;

#[MediaConversion(name: 'thumb', width: 400)]
class TestModelWithInheritedMediaAttributes extends TestModelWithBaseMediaAttributes {}
