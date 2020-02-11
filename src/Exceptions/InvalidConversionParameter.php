<?php

namespace Spatie\Medialibrary\Exceptions;

use Exception;

class InvalidConversionParameter extends Exception
{
    public static function invalidWidth(): self
    {
        return new static('Width should be numeric and greater than 0');
    }

    public static function invalidHeight(): self
    {
        return new static('Height should be numeric and greater than 0');
    }

    public static function invalidFormat(string $givenFormat, array $validFormats): self
    {
        $validFormats = implode(', ', $validFormats);

        return new static("Format `{$givenFormat}` is not one of the allowed formats: {$validFormats}");
    }

    public static function invalidFit(string $givenFit, array $givenFits): self
    {
        $givenFits = implode(', ', $givenFits);

        return new static("Format `{$givenFit}` is not one of the allowed formats: {$givenFits}");
    }

    public static function shouldBeNumeric(string $name, $value): self
    {
        return new static("{$name} should be numeric. `{$value}` given.");
    }

    public static function shouldBeGreaterThanOne(string $name, $value): self
    {
        return new static("{$name} should be greater than one. `{$value}` given.");
    }
}
