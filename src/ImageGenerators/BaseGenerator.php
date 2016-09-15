<?php

namespace Spatie\MediaLibrary\ImageGenerators;

use Spatie\MediaLibrary\ImageGenerator\ImageGenerator;

abstract class BaseGenerator implements ImageGenerator
{
    public function canConvert(string $path): bool
    {
        if (! file_exists($path)) {
            return false;
        }

        if (! $this->areRequirementsInstalled()) {
            return false;
        }

        if ($this->supportedExtensions()->contains(pathinfo($path, PATHINFO_EXTENSION))) {
            return true;
        }

        if ($this->supportedMimetypes()->contains(File::getMimetype($path))) {
            return true;
        }

        if ($this->supportedTypes()->contains($media->type)) {
            return true;
        }

        return false;
    }
}