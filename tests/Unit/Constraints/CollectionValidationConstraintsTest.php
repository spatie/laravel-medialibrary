<?php

namespace Spatie\MediaLibrary\Tests\Unit\Extension\UrlGenerator;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Exceptions\CollectionNotFound;
use Spatie\MediaLibrary\Exceptions\ConversionsNotFound;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestCase;

class CollectionValidationConstraintsTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_when_it_is_called_with_non_existing_collection()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $this->expectException(CollectionNotFound::class);
        $testModel->validationConstraints('logo');
    }

    /**
     * @test
     */
    public function it_throws_exception_when_it_is_called_with_non_existent_conversions()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }
        };
        $this->expectException(ConversionsNotFound::class);
        $testModel->validationConstraints('logo');
    }

    /**
     * @test
     */
    public function it_returns_no_validation_constraint_when_none_is_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo');
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb');
            }
        };
        $validationConstraintsString = $testModel->validationConstraints('logo');
        $this->assertEquals('', $validationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_only_dimension_validation_constraints_when_only_dimensions_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo');
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $validationConstraintsString = $testModel->validationConstraints('logo');
        $this->assertEquals('dimensions:min_width=60,min_height=20', $validationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_only_mime_types_validation_constraints_when_only_mime_types_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb');
            }
        };
        $validationConstraintsString = $testModel->validationConstraints('logo');
        $this->assertEquals('mimetypes:image/jpeg,image/png', $validationConstraintsString);
    }
}
