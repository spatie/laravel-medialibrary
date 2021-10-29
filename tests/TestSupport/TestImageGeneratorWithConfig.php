<?php

namespace Spatie\MediaLibrary\Tests\TestSupport;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGenerator;

class TestImageGeneratorWithConfig extends ImageGenerator
{
    public string $firstName;

    public string $secondName;

    public function __construct(string $firstName, string $secondName)
    {
        $this->firstName = $firstName;

        $this->secondName = $secondName;
    }

    public function convert(string $file, Conversion $conversion = null): string
    {
        // TODO: Implement convert() method.
    }

    public function requirementsAreInstalled(): bool
    {
        // TODO: Implement requirementsAreInstalled() method.
    }

    public function supportedExtensions(): Collection
    {
        // TODO: Implement supportedExtensions() method.
    }

    public function supportedMimeTypes(): Collection
    {
        // TODO: Implement supportedMimeTypes() method.
    }
}
