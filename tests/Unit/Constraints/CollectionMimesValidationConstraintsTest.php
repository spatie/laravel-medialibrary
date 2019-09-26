<?php

namespace Spatie\MediaLibrary\Tests\Unit\Extension\UrlGenerator;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Exceptions\CollectionNotFound;
use Spatie\MediaLibrary\File;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestCase;

class CollectionMimesValidationConstraintsTest extends TestCase
{
    /**
     * @test
     */
    public function it_return_none_when_it_is_called_with_non_existing_collection()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $mimesValidationConstraintsString = $testModel->mimesValidationConstraints('logo');
        $this->assertEquals('', $mimesValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_mimes_validation_constraints_when_declared_in_collection()
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
        $mimesValidationConstraintsString = $testModel->mimesValidationConstraints('logo');
        $this->assertEquals('mimes:jpeg,jpg,png,pdf', $mimesValidationConstraintsString);
    }

    /**
     * @test
     */
    public function it_returns_no_collection_mimes_validation_constraints_when_none_declared()
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
        $mimesValidationConstraintsString = $testModel->mimesValidationConstraints('logo');
        $this->assertEquals('', $mimesValidationConstraintsString);
    }
}
