<?php

namespace Spatie\MediaLibrary\Tests\Support;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\ImageGenerators\BaseGenerator;

class TestImageGenerator extends BaseGenerator
{
    public $supportedMimetypes;
    public $supportedExtensions;
    public $shouldMatchBothExtensionsAndMimetypes = false;

    public function __construct()
    {
        $this->supportedExtensions = new Collection();
        $this->supportedMimetypes = new Collection();
    }

    public function supportedExtensions(): Collection
    {
        return $this->supportedExtensions;
    }

    public function supportedMimetypes(): Collection
    {
        return $this->supportedMimetypes;
    }

    public function shouldMatchBothExtensionsAndMimeTypes(): bool
    {
        return $this->shouldMatchBothExtensionsAndMimetypes;
    }

    public function convert(string $path, Conversion $conversion = null): string
    {
        return $path;
    }

    public function requirementsAreInstalled(): bool
    {
        return true;
    }
}
