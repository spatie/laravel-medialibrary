<?php

namespace Spatie\MediaLibrary\Tests\TestSupport;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGenerator;

class TestImageGeneratorWithConfig extends ImageGenerator
{
    public string $test;

    public string $test2;

    public function __construct(string $test, string $test2)
    {
        $this->test = $test;

        $this->test2 = $test2;
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
