<?php

namespace Spatie\MediaLibrary\ResponsiveImages\WidthCalculator;

use Illuminate\Support\Collection;
use Spatie\Image\Image;

interface WidthCalculator
{
    public function calculateWidths(string $imagePath): Collection;
}
