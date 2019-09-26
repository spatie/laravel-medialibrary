<?php

namespace Spatie\MediaLibrary\Tests\Unit\Extension\UrlGenerator;

use Spatie\MediaLibrary\File;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestCase;

class MimeTypesLegendTest extends TestCase
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
        $dimensionsLegendString = $testModel->mimeTypesLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_no_mime_types_legend_when_none_declared()
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
        $dimensionsLegendString = $testModel->mimeTypesLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function it_returns_mime_types_legend_when_are_declared()
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
        $dimensionsLegendString = $testModel->mimeTypesLegend('logo');
        $this->assertEquals(trans_choice('medialibrary::medialibrary.constraint.types', 3, [
            'types' => 'jpeg, jpg, png',
        ]), $dimensionsLegendString);
    }
}
