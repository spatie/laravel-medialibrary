<?php

namespace Programic\MediaLibrary\Tests\TestSupport;

use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\Support\PathGenerator\PathGenerator;
use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class TestCustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $entry = TestModel::find($media->model_id);

        return "some_user/{$media->model_id}/";
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'custom_conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'custom_responsive_images/';
    }
}
