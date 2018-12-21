<?php

namespace Spatie\MediaLibrary\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\Tests\TestCase;

class CollectionLegendTest extends TestCase
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
        $this->testModelWithGlobalConversionOnlyWithoutCollection->constraintsLegend('logo');
    }

    /**
     * @test
     * @expectedException \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     * @expectedExceptionMessage No conversion declared in the
     *                           Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithoutMediaConversions-model
     */
    public function it_throws_exception_when_it_is_called_with_inexistant_conversions()
    {
        $this->testModelWithCollectionWithoutConversions->constraintsLegend('logo');
    }

    /**
     * @test
     */
    public function it_returns_no_legend_when_no_constraint_is_declared()
    {
        $legendString = $this->testModelWithGlobalConversionWithNoSizeAndNoMimeTypes->constraintsLegend('logo');
        $this->assertEquals('', $legendString);
    }

    /**
     * @test
     */
    public function it_returns_only_dimension_legend_when_only_dimensions_declared()
    {
        $legendString = $this->testModelWithGlobalConversionOnly->constraintsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.both', [
            'width'  => 60,
            'height' => 20,
        ]), $legendString);
    }

    /**
     * @test
     */
    public function it_returns_only_mime_types_legend_when_only_mime_types_declared()
    {
        $legendString = $this->testModelWithGlobalConversionWithNoSize->constraintsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.mimeTypes', [
            'mimetypes'  => 'image/jpeg, image/png',
        ]), $legendString);
    }
}
