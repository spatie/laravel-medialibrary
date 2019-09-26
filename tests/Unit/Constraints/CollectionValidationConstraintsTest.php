<?php

namespace Spatie\MediaLibrary\Tests\Unit\Extension\UrlGenerator;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestCase;

class CollectionValidationConstraintsTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_no_validation_constraint_when_non_existing_collection()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $validationConstraintsArray = $testModel->validationConstraints('logo');
        $this->assertEquals([], $validationConstraintsArray);
    }

    /**
     * @test
     */
    public function it_returns_no_validation_constraint_with_non_existent_conversions()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }
        };
        $validationConstraintsArray = $testModel->validationConstraints('logo');
        $this->assertEquals([], $validationConstraintsArray);
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
        $validationConstraintsArray = $testModel->validationConstraints('logo');
        $this->assertEquals([], $validationConstraintsArray);
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
        $validationConstraintsArray = $testModel->validationConstraints('logo');
        $this->assertEquals(['dimensions:min_width=60,min_height=20'], $validationConstraintsArray);
    }

    /**
     * @test
     */
    public function it_returns_only_mime_types_and_mimes_validation_constraints_when_only_mime_types_declared()
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
        $validationConstraintsArray = $testModel->validationConstraints('logo');
        $this->assertEquals(['mimetypes:image/jpeg,image/png', 'mimes:jpeg,jpg,png'], $validationConstraintsArray);
    }

    /**
     * @test
     */
    public function it_returns_all_declared_validation_constraints()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $validationConstraintsArray = $testModel->validationConstraints('logo');
        $this->assertEquals(
            ['mimetypes:image/jpeg,image/png', 'mimes:jpeg,jpg,png', 'dimensions:min_width=60,min_height=20'],
            $validationConstraintsArray
        );
    }
}
