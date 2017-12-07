<?php

namespace Spatie\MediaLibrary\ResponsiveImages\WidthCalculator;

use Illuminate\Support\Collection;
use Spatie\Image\Image;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator;

class FileSizeOptimizedWidthCalculator implements WidthCalculator
{
    public function calculateWidthsFromFile(string $imagePath): Collection
    {
        $image = Image::load($imagePath);
        
        $width = $image->getWidth();
        $height = $image->getHeight();
        $filesize = filesize($imagePath);

        return $this->calculateWidths($filesize, $width, $height);
    }

    public function calculateWidths(int $filesize, int $width, int $height): Collection
    {
        $targetWidths = collect();

        $targetWidths->push($width);

        $ratio = $height / $width;
        $area = $width * $width * $ratio;

        $predictedFileSize = $filesize;
        $pixelPrice = $predictedFileSize / $area;

        while (true) {
            $predictedFileSize *= 0.7;

            $newWidth = (int)floor(sqrt(($predictedFileSize / $pixelPrice) / $ratio));

            if ($this->finishedCalulating($predictedFileSize, $newWidth)) {
                return $targetWidths;
            }

            $targetWidths->push($newWidth);
        }
    }

    protected function finishedCalulating(int $predictedFileSize, int $newWidth): bool
    {
        if ($newWidth < 20) {
            return true;
        }

        if ($predictedFileSize < (1024 * 10)) {
            return true;
        }

        return false;
    }
}
