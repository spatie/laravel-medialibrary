<?php

namespace Spatie\MediaLibrary\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\Tests\TestCase;

class CollectionMimeTypesValidationConstraintsTest extends TestCase
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
        $this->testModelWithGlobalConversionOnlyWithoutCollection->mimeTypesValidationConstraints('logo');
    }

    /**
     * @test
     */
    public function it_returns_mime_types_validation_constraints_when_declared_in_collection()
    {
        $mimeTypesValidationConstraintsString = $this->testModelWithGlobalAndCollectionConversions->mimeTypesValidationConstraints('logo');
        $this->assertEquals('mimetypes:image/jpeg,image/png', $mimeTypesValidationConstraintsString);
    }
    
    /**
     * @test
     */
    public function it_returns_no_collection_mime_types_validation_constraints_when_none_declared()
    {
        $mimeTypesValidationConstraintsString = $this->testModelWithGlobalConversionOnly->mimeTypesValidationConstraints('logo');
        $this->assertEquals('', $mimeTypesValidationConstraintsString);
    }
}
