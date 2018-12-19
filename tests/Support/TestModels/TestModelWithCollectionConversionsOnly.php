<?php

namespace Spatie\MediaLibrary\Tests\Support\TestModels;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;

class TestModelWithCollectionConversionsOnly extends TestModel
{
    /**
     * Register the media collections.
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @return void
     */
    public function registerMediaCollections()
    {
        $this->addMediaCollection('logo')
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->registerMediaConversions(function(Media $media = null) {
                $this->addMediaConversion('admin-panel')
                    ->crop(Manipulations::CROP_CENTER, 100, 140);
                $this->addMediaConversion('mail')
                    ->crop(Manipulations::CROP_CENTER, 120, 100);
            });
    }

    /**
     * Register the media conversions.
     *
     * @param \Spatie\MediaLibrary\Models\Media|null $media
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 40, 40);
    }
}
