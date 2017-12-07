<?php

namespace Spatie\MediaLibrary\Tests\Media;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\FileSizeOptimizedWidthCalculator;

class FileSizeOptimizedWidthCalculatorTest extends TestCase
{
    /** @test */
    public function it_can_calculate_the_dimensions()
    {
        $dimensions = (new FileSizeOptimizedWidthCalculator())->calculateWidths($this->getTestJpg());

        $this->assertEquals([
            0 => 340,
            1 => 304,
            2 => 263,
            3 => 215,
            4 => 152,
        ], $dimensions->toArray());

        $dimensions = (new FileSizeOptimizedWidthCalculator())->calculateWidths($this->getSmallTestJpg());

        $this->assertEquals([
            0 => 150,
            1 => 134,
            2 => 116,
            3 => 94,
            4 => 67,
        ], $dimensions->toArray());
    }
}
