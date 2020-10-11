<?php

namespace Spatie\MediaLibrary\ResponsiveImages\WidthCalculator;

use Illuminate\Support\Collection;

class FixedWidthCalculator extends FileSizeOptimizedWidthCalculator
{
    public function calculateWidths(int $fileSize, int $width, int $height): Collection
    {
        $breakpoints = config('media-library.responsive_images.breakpoints');

        return empty($breakpoints) ? parent::calculateWidths($fileSize, $width, $height) : collect($breakpoints)->filter(function ($value, $key) use ($width) {
            return $value < $width;
        });
    }
}
