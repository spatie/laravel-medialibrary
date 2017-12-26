<?php

namespace Spatie\MediaLibrary\ResponsiveImages\WidthCalculator;

use Illuminate\Support\Collection;
use Spatie\Image\Image;

interface WidthCalculator
{
    public function calculateWidthsFromFile(string $imagePath): Collection;

    public function calculateWidths(int $filesize, int $width, int $height): Collection;
}
