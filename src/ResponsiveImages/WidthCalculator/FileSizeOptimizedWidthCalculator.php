<?php

namespace Spatie\MediaLibrary\ResponsiveImages\WidthCalculator;

use Illuminate\Support\Collection;
use Spatie\Image\Image;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator;

class FileSizeOptimizedWidthCalculator implements WidthCalculator
{
    public function calculateWidths(string $imagePath): Collection
    {
        $image = Image::load($imagePath);

        $width = $image->getWidth();
        $height = $image->getHeight();
       
        $ratio = $height / $width;
        $area = $width * $width * $ratio;

        $fileSize = filesize($imagePath);
        $pixelPrice = $fileSize / $area;
        $stepModifier = $fileSize * 0.2;

        $targetWidths = collect();

        while ($fileSize > 0) {
            $newWidth = floor(sqrt(($fileSize / $pixelPrice) / $ratio));

            if ($newWidth > 0) {
                $targetWidths->push($newWidth);
            }

            $fileSize -= $stepModifier;
        }

        return $targetWidths;
    }
}
