<?php

namespace Spatie\MediaLibrary\ResponsiveImages\WidthCalculator;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Support\ImageFactory;

class MaxWidthWidthCalculator implements WidthCalculator
{

    public function __construct(public array $maxWidth)
    {
    }

    public function calculateWidthsFromFile(string $imagePath): Collection
    {
        $image = ImageFactory::load($imagePath);

        $width = $image->getWidth();
        $height = $image->getHeight();
        $fileSize = filesize($imagePath);

        return $this->calculateWidths($fileSize, $width, $height);
    }

    public function calculateWidths(int $fileSize, int $width, int $height): Collection
    {
        $targetWidths = collect();

        $width = min($this->maxWidth, $width);

        $targetWidths->push($width);

        while (true) {
            $width = (int) $width * 0.6;

            if ($width <= 20) {
                return $targetWidths;
            }

            $targetWidths->push($width);
        }
    }

}
