<?php

namespace Spatie\MediaLibrary\Test;

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
            ->setCrop(50, 50, 10, 10)
            ->nonQueued();
    }
}
