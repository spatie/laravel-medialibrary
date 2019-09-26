<?php

namespace Spatie\MediaLibrary\Tests\Unit\Extension\UrlGenerator;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestCase;

class ConstraintsLegendTest extends TestCase
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
        $legendString = $testModel->constraintsLegend('logo');
        $this->assertEquals('', $legendString);
    }

    /**
     * @test
     */
    public function it_returns_none_when_it_is_called_with_non_existing_conversions()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }
        };
        $legendString = $testModel->constraintsLegend('logo');
        $this->assertEquals('', $legendString);
    }

    /**
     * @test
     */
    public function it_returns_no_legend_when_no_constraint_is_declared()
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
        $legendString = $testModel->constraintsLegend('logo');
        $this->assertEquals('', $legendString);
    }

    /**
     * @test
     */
    public function it_returns_only_dimension_legend_when_only_dimensions_declared()
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
        $legendString = $testModel->constraintsLegend('logo');
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
        $legendString = $testModel->constraintsLegend('logo');
        $this->assertEquals(trans_choice('medialibrary::medialibrary.constraint.types', 3, [
            'types' => 'jpeg, jpg, png',
        ]), $legendString);
    }

    /**
     * @test
     */
    public function it_returns_only_mime_types_legend_when_no_image_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['application/pdf']);
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $legendString = $testModel->constraintsLegend('logo');
        $this->assertEquals(trans_choice('medialibrary::medialibrary.constraint.types', 1, [
            'types' => 'pdf',
        ]), $legendString);
    }

    /**
     * @test
     */
    public function it_returns_only_mime_types_legend_when_images_and_files_are_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $legendString = $testModel->constraintsLegend('logo');
        $this->assertEquals(trans_choice('medialibrary::medialibrary.constraint.types', 4, [
            'types' => 'jpeg, jpg, png, pdf',
        ]), $legendString);
    }
}
