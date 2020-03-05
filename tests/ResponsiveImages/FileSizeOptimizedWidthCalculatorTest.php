<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\FileSizeOptimizedWidthCalculator;
use Spatie\MediaLibrary\Tests\TestCase;

class FileSizeOptimizedWidthCalculatorTest extends TestCase
{
    /** @test */
    public function it_can_calculate_the_optimized_widths_from_a_file()
    {
        $dimensions = (new FileSizeOptimizedWidthCalculator())->calculateWidthsFromFile($this->getTestJpg());

        $this->assertEquals([
            0 => 340,
            1 => 284,
            2 => 237,
        ], $dimensions->toArray());

        $dimensions = (new FileSizeOptimizedWidthCalculator())->calculateWidthsFromFile($this->getSmallTestJpg());

        $this->assertEquals([
            0 => 150,
        ], $dimensions->toArray());
    }

    /** @test */
    public function it_can_calculate_the_optimized_widths_for_different_dimensions()
    {
        $dimensions = (new FileSizeOptimizedWidthCalculator())->calculateWidths(300 * 1024, 300, 200);

        $this->assertEquals([
            0 => 300,
            1 => 250,
            2 => 210,
            3 => 175,
            4 => 147,
            5 => 122,
            6 => 102,
            7 => 86,
            8 => 72,
            9 => 60,
        ], $dimensions->toArray());

        $dimensions = (new FileSizeOptimizedWidthCalculator())->calculateWidths(3000 * 1024, 2400, 1800);

        $this->assertEquals([
            0 => 2400,
            1 => 2007,
            2 => 1680,
            3 => 1405,
            4 => 1176,
            5 => 983,
            6 => 823,
            7 => 688,
            8 => 576,
            9 => 482,
            10 => 403,
            11 => 337,
            12 => 282,
            13 => 236,
            14 => 197,
            15 => 165,
        ], $dimensions->toArray());

        $dimensions = (new FileSizeOptimizedWidthCalculator())->calculateWidths(12000 * 1024, 8200, 5500);

        $this->assertEquals([
            0 => 8200,
            1 => 6860,
            2 => 5740,
            3 => 4802,
            4 => 4017,
            5 => 3361,
            6 => 2812,
            7 => 2353,
            8 => 1968,
            9 => 1647,
            10 => 1378,
            11 => 1153,
            12 => 964,
            13 => 807,
            14 => 675,
            15 => 565,
            16 => 472,
            17 => 395,
            18 => 330,
            19 => 276,
        ], $dimensions->toArray());
    }
}
