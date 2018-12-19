<?php

namespace Spatie\MediaLibrary\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\Tests\TestCase;

class CollectionDimensionLegendTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     * @expectedException \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     * @expectedExceptionMessage No collection `logo` declared in the
     *                           Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection-model
     */
    public function it_throws_exception_when_it_is_called_with_inexistant_collection()
    {
        $this->testModelWithGlobalConversionOnlyWithoutCollection->collectionDimensionsLegend('logo');
    }

    /**
     * @test
     * @expectedException \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     * @expectedExceptionMessage No conversion declared in the
     *                           Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithoutMediaConversions-model
     */
    public function it_throws_exception_when_it_is_called_with_inexistant_conversions()
    {
        $this->testModelWithCollectionWithoutConversions->collectionDimensionsLegend('logo');
    }

    /**
     * @test
     */
    public function it_returns_only_width_dimension_legend_when_only_width_is_declared()
    {
        $dimensionsLegendString = $this->testModelWithGlobalConversionWithOnlyWidth->collectionDimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.width', [
            'width'  => 120,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_only_height_dimension_legend_when_only_height_is_declared()
    {
        $dimensionsLegendString = $this->testModelWithGlobalConversionWithOnlyHeight->collectionDimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.height', [
            'height'  => 30,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_no_dimension_legend_when_no_size_is_declared()
    {
        $dimensionsLegendString = $this->testModelWithGlobalConversionWithNoSize->collectionDimensionsLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_width_and_height_dimension_legend_when_both_are_declared()
    {
        $dimensionsLegendString = $this->testModelWithGlobalAndCollectionConversions->collectionDimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.both', [
            'width'  => 100,
            'height' => 80,
        ]), $dimensionsLegendString);
    }
}
