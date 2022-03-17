<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\Support\PathGenerator\CustomPathGenerator;

class TestModelWithConversionAndCustomPathGenerator extends TestModel
{
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(50)
            ->nonQueued();

        $this->addMediaConversion('keep_original_format')
            ->keepOriginalImageFormat()
            ->nonQueued();
    }

    /** @return class-string<\Spatie\MediaLibrary\Support\PathGenerator\PathGenerator> */
    public function getPathGeneratorClass(): string
    {
        return CustomPathGenerator::class;
    }
}
