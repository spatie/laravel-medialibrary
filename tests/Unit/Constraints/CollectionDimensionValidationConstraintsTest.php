<?php

namespace Spatie\MediaLibrary\Tests\Unit\Extension\UrlGenerator;

use Spatie\MediaLibrary\File;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestCase;

class CollectionDimensionValidationConstraintsTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_none_when_it_is_called_with_non_existing_collection()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_none_when_it_is_called_with_non_existent_conversions()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }
        };
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_global_conversion_dimension_validation_constraints_when_no_collection_conversions_declared()
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
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=60,min_height=20', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_only_width_dimension_validation_constraint_when_only_width_is_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->width(120);
            }
        };
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=120', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_only_height_dimension_validation_constraint_when_only_height_is_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->height(30);
            }
        };
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_height=30', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_no_dimension_validation_constraint_when_no_size_is_declared()
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
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_collection_dimension_validation_constraints_when_no_global_conversions_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')
                    ->acceptsMimeTypes(['image/jpeg', 'image/png'])
                    ->registerMediaConversions(function (Media $media = null) {
                        $this->addMediaConversion('admin-panel')
                            ->crop(Manipulations::CROP_CENTER, 100, 140);
                        $this->addMediaConversion('mail')
                            ->crop(Manipulations::CROP_CENTER, 120, 100);
                    });
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 40, 40);
            }
        };
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=120,min_height=140', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_global_and_collection_dimension_validation_constraints_when_both_are_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')
                    ->acceptsFile(function (File $file) {
                        return true;
                    })
                    ->acceptsMimeTypes(['image/jpeg', 'image/png'])
                    ->registerMediaConversions(function (Media $media = null) {
                        $this->addMediaConversion('admin-panel')
                            ->crop(Manipulations::CROP_CENTER, 20, 80);
                    });
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 100, 70);
            }
        };
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=100,min_height=80', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_does_not_returns_dimension_validation_constraints_when_no_image_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')
                    ->acceptsFile(function (File $file) {
                        return true;
                    })
                    ->acceptsMimeTypes(['application/pdf'])
                    ->registerMediaConversions(function (Media $media = null) {
                        $this->addMediaConversion('admin-panel')
                            ->crop(Manipulations::CROP_CENTER, 20, 80);
                    });
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 100, 70);
            }
        };
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('', $dimensionsValidationConstraintsString);
    }
}
