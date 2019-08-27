<?php

namespace Spatie\MediaLibrary\Tests\Unit\Extension\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\CollectionNotFound;
use Spatie\MediaLibrary\File;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestCase;

class CollectionMimeTypesValidationConstraintsTest extends TestCase
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
        $testModel->mimeTypesValidationConstraints('logo');
    }

    /**
     * @test
     */
    public function it_returns_mime_types_validation_constraints_when_declared_in_collection()
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
        $mimeTypesValidationConstraintsString = $testModel->mimeTypesValidationConstraints('logo');
        $this->assertEquals('mimetypes:image/jpeg,image/png', $mimeTypesValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_no_collection_mime_types_validation_constraints_when_none_declared()
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
        $mimeTypesValidationConstraintsString = $testModel->mimeTypesValidationConstraints('logo');
        $this->assertEquals('', $mimeTypesValidationConstraintsString);
    }
}
