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
            0 => 340.0,
            1 => 304.0,
            2 => 263.0,
            3 => 215.0,
            4 => 152.0,
        ], $dimensions->toArray());
    }
}
