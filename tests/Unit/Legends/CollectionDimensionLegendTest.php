<?php

namespace Spatie\MediaLibrary\Tests\Unit\Extension\UrlGenerator;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\File;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestCase;

class CollectionDimensionLegendTest extends TestCase
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_only_width_dimension_legend_when_only_width_is_declared()
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.width', [
            'width' => 120,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_only_height_dimension_legend_when_only_height_is_declared()
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.height', [
            'height' => 30,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_no_dimension_legend_when_no_size_is_declared()
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_width_and_height_dimension_legend_when_both_are_declared()
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.both', [
            'width'  => 100,
            'height' => 80,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_does_not_returns_dimensions_legend_when_no_image_declared()
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }
}
