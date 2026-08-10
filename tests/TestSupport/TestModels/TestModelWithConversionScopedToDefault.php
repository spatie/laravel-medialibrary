<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Attributes\MediaConversion;

#[MediaConversion(name: 'square', collections: ['default'], width: 100)]
class TestModelWithConversionScopedToDefault extends TestModel {}
