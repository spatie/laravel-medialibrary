<?php

namespace Spatie\MediaLibrary\Tests\Unit\Extension\UrlGenerator;

use Spatie\MediaLibrary\File;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestCase;

class CollectionMaxSizeTest extends TestCase
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertEquals([], $maxSizes);
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertEquals([], $maxSizes);
    }

    /**
     * @test
     */
    public function it_returns_global_conversion_max_sizes_when_no_collection_conversions_declared()
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertEquals(60, $maxSizes['width']);
        $this->assertEquals(20, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_only_width_when_only_width_is_declared()
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertEquals(120, $maxSizes['width']);
        $this->assertNull($maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_only_height_when_only_height_is_declared()
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertNull($maxSizes['width']);
        $this->assertEquals(30, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_no_size_when_none_is_declared()
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertNull($maxSizes['width']);
        $this->assertNull($maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_collection_conversions_max_sizes_when_no_global_conversions_declared()
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertEquals(120, $maxSizes['width']);
        $this->assertEquals(140, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_global_and_collection_conversions_max_sizes_when_both_are_declared()
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertEquals(100, $maxSizes['width']);
        $this->assertEquals(80, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_no_image_declared()
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertEquals([], $maxSizes);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_mime_type_different_from_image_is_declared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')
                    ->acceptsFile(function (File $file) {
                        return true;
                    })
                    ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf'])
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
        $maxSizes = $testModel->collectionMaxSizes('logo');
        $this->assertEquals([], $maxSizes);
    }
}
