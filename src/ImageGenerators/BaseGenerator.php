<?php

namespace Spatie\MediaLibrary\ImageGenerators;

use Spatie\MediaLibrary\ImageGenerator\ImageGenerator;
use Spatie\MediaLibrary\Media;

abstract class BaseGenerator implements ImageGenerator
{
    public function canConvert(Media $media): bool
    {
        if (! $this->areRequirementsInstalled()) {
            return false;
        }

        if ($this->supportedExtensions()->contains($media->extension)) {
            return true;
        }

        if ($this->supportedMimetypes()->contains($media->mime)) {
            return true;
        }

        if ($this->supportedTypes()->contains($media->type)) {
            return true;
        }

        return false;
    }
}