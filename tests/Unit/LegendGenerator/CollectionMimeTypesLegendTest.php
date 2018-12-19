<?php

namespace Spatie\MediaLibrary\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\Tests\TestCase;

class CollectionMimeTypesLegendTest extends TestCase
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
        $this->testModelWithGlobalConversionOnlyWithoutCollection->collectionMimeTypesLegend('logo');
    }

    /**
     * @test
     */
    public function it_returns_no_mime_types_legend_when_none_declared()
    {
        $dimensionsLegendString = $this->testModelWithGlobalConversionOnly->collectionMimeTypesLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_mime_types_legend_when_are_declared()
    {
        $dimensionsLegendString = $this->testModelWithGlobalAndCollectionConversions->collectionMimeTypesLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.mimeTypes', [
            'mimetypes'  => 'image/jpeg, image/png',
        ]), $dimensionsLegendString);
    }
}
