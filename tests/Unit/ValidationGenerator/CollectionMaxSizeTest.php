<?php

namespace Spatie\MediaLibrary\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\Tests\TestCase;

class CollectionMaxSizeTest extends TestCase
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
        $this->testModelWithGlobalConversionOnlyWithoutCollection->collectionMaxSizes('logo');
    }

    /**
     * @test
     * @expectedException \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     * @expectedExceptionMessage No conversion declared in the
     *                           Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithoutMediaConversions-model
     */
    public function it_throws_exception_when_it_is_called_with_inexistant_conversions()
    {
        $this->testModelWithCollectionWithoutConversions->collectionMaxSizes('logo');
    }

    /**
     * @test
     */
    public function it_returns_global_conversion_max_sizes_when_no_collection_conversions_declared()
    {
        $maxSizes = $this->testModelWithGlobalConversionOnly->collectionMaxSizes('logo');
        $this->assertEquals(60, $maxSizes['width']);
        $this->assertEquals(20, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_only_width_when_only_width_is_declared()
    {
        $maxSizes = $this->testModelWithGlobalConversionWithOnlyWidth->collectionMaxSizes('logo');
        $this->assertEquals(120, $maxSizes['width']);
        $this->assertNull($maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_only_height_when_only_height_is_declared()
    {
        $maxSizes = $this->testModelWithGlobalConversionWithOnlyHeight->collectionMaxSizes('logo');
        $this->assertNull($maxSizes['width']);
        $this->assertEquals(30, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_no_size_when_none_is_declared()
    {
        $maxSizes = $this->testModelWithGlobalConversionWithNoSize->collectionMaxSizes('logo');
        $this->assertNull($maxSizes['width']);
        $this->assertNull($maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_collection_conversions_max_sizes_when_no_global_conversions_declared()
    {
        $maxSizes = $this->testModelWithCollectionConversionsOnly->collectionMaxSizes('logo');
        $this->assertEquals(120, $maxSizes['width']);
        $this->assertEquals(140, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_global_and_collection_conversions_max_sizes_when_both_are_declared()
    {
        $maxSizes = $this->testModelWithGlobalAndCollectionConversions->collectionMaxSizes('logo');
        $this->assertEquals(100, $maxSizes['width']);
        $this->assertEquals(80, $maxSizes['height']);
    }
}
