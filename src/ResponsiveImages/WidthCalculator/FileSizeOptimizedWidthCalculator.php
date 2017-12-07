<?php

namespace Spatie\MediaLibrary\ResponsiveImages\WidthCalculator;

use Illuminate\Support\Collection;
use Spatie\Image\Image;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator;

class FileSizeOptimizedWidthCalculator implements WidthCalculator
{
    public function calculateWidths(string $imagePath): Collection
    {
        $targetWidths = collect();

        $image = Image::load($imagePath);

        $width = $image->getWidth();
        $height = $image->getHeight();

        $targetWidths->push($width);

        $ratio = $height / $width;
        $area = $width * $width * $ratio;

        $predictedFileSize = filesize($imagePath);
        $pixelPrice = $predictedFileSize / $area;
        $stepModifier = $predictedFileSize * 0.2;

        while (true) {
            $predictedFileSize -= $stepModifier;

            $newWidth = (int)floor(sqrt(($predictedFileSize / $pixelPrice) / $ratio));

            if ($this->finishedCalulating($predictedFileSize, $newWidth)) {
                return $targetWidths;
            }

            $targetWidths->push($newWidth);
        }
    }

    protected function finishedCalulating(int $predictedFileSize, int $newWidth): bool
    {
        if ($newWidth < 50) {
            return true;
        }

        if ($predictedFileSize < (1024 / 20)) {
            return true;
        }

        return false;
    }
}
