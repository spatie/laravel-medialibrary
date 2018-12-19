<?php

namespace Spatie\MediaLibrary\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\Tests\TestCase;

class CollectionValidationConstraintsTest extends TestCase
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
        $this->testModelWithGlobalConversionOnlyWithoutCollection->validationConstraints('logo');
    }

    /**
     * @test
     * @expectedException \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     * @expectedExceptionMessage No conversion declared in the
     *                           Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithoutMediaConversions-model
     */
    public function it_throws_exception_when_it_is_called_with_inexistant_conversions()
    {
        $this->testModelWithCollectionWithoutConversions->validationConstraints('logo');
    }

    /**
     * @test
     */
    public function it_returns_no_validation_constraint_when_none_is_declared()
    {
        $validationConstraintsString = $this->testModelWithGlobalConversionWithNoSizeAndNoMimeTypes->validationConstraints('logo');
        $this->assertEquals('', $validationConstraintsString);
    }
    
    /**
     * @test
     */
    public function it_returns_only_dimension_validation_constraints_when_only_dimensions_declared()
    {
        $validationConstraintsString = $this->testModelWithGlobalConversionOnly->validationConstraints('logo');
        $this->assertEquals('dimensions:min_width=60,min_height=20', $validationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_only_mime_types_validation_constraints_when_only_mime_types_declared()
    {
        $validationConstraintsString = $this->testModelWithGlobalConversionWithNoSize->validationConstraints('logo');
        $this->assertEquals('mimetypes:image/jpeg,image/png', $validationConstraintsString);
    }
}
