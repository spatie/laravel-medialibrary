<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\WidthCalculators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator;

class FixedWidthCalculator implements WidthCalculator
{
    public function __construct(public array $widths) {}

    public function calculateWidthsFromFile(string $imagePath): Collection
    {

        return $this->calculateWidths(0, 0, 0);
    }

    public function calculateWidths(int $fileSize, int $width, int $height): Collection
    {
        return collect($this->widths);
    }
}
