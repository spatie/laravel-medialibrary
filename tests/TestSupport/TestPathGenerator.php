<?php

namespace Spatie\MediaLibrary\Tests\TestSupport;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class TestPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $entry = TestModel::find($media->model_id);

        $fileFolder = md5($media->id);

        return "{$entry->id}/{$fileFolder}/";
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'/custom_conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'/custom_responsive_images/';
    }
}
