<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Spatie\MediaLibrary\Tests\Support\PathGenerator\CustomPathGenerator;

class TestModelWithMorphMapInSideModel extends TestModel
{
    protected static function booting(): void
    {
        PathGeneratorFactory::setCustomPathGenerators(static::class, CustomPathGenerator::class);
    }
}
