<?php

namespace Spatie\MediaLibrary\Test;

use Spatie\Image\Manipulations;

class TestModelWithConversion extends TestModel
{
    /**
     * Register the conversions that should be performed.
     *
     * @return array
     */
    public function registerMediaConversions()
    {
        $this->addMediaConversion('thumb')
            ->setManipulations(function(Manipulations $manipulations) {
                $manipulations->crop(Manipulations::CROP_CENTER, 50, 50);
            })
            ->nonQueued();
    }
}
