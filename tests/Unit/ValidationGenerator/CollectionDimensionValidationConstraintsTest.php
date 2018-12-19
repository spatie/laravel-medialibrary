<?php

namespace Spatie\MediaLibrary\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\Tests\TestCase;

class CollectionDimensionValidationConstraintsTest extends TestCase
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
        $this->testModelWithGlobalConversionOnlyWithoutCollection->dimensionValidationConstraints('logo');
    }

    /**
     * @test
     * @expectedException \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     * @expectedExceptionMessage No conversion declared in the
     *                           Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithoutMediaConversions-model
     */
    public function it_throws_exception_when_it_is_called_with_inexistant_conversions()
    {
        $this->testModelWithCollectionWithoutConversions->dimensionValidationConstraints('logo');
    }

    /**
     * @test
     */
    public function it_returns_global_conversion_dimension_validation_constraints_when_no_collection_conversions_declared()
    {
        $dimensionsValidationConstraintsString = $this->testModelWithGlobalConversionOnly->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=60,min_height=20', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_only_width_dimension_validation_constraint_when_only_width_is_declared()
    {
        $dimensionsValidationConstraintsString = $this->testModelWithGlobalConversionWithOnlyWidth->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=120', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_only_height_dimension_validation_constraint_when_only_height_is_declared()
    {
        $dimensionsValidationConstraintsString = $this->testModelWithGlobalConversionWithOnlyHeight->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_height=30', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_no_dimension_validation_constraint_when_no_size_is_declared()
    {
        $dimensionsValidationConstraintsString = $this->testModelWithGlobalConversionWithNoSize->dimensionValidationConstraints('logo');
        $this->assertEquals('', $dimensionsValidationConstraintsString);
    }
    
    /**
     * @test
     */
    public function it_returns_collection_dimension_validation_constraints_when_no_global_conversions_declared()
    {
        $dimensionsValidationConstraintsString = $this->testModelWithCollectionConversionsOnly->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=120,min_height=140', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_global_and_collection_dimension_validation_constraints_when_both_are_declared()
    {
        $dimensionsValidationConstraintsString = $this->testModelWithGlobalAndCollectionConversions->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=100,min_height=80', $dimensionsValidationConstraintsString);
    }
}
