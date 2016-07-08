<?php

namespace Spatie\MediaLibrary\Test;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;

class SecondTestModel extends Model implements HasMediaConversions
{
    use HasMediaTrait;

    protected $table = 'second_test_models';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * Register the conversions that should be performed.
     *
     * @return array
     */
    public function registerMediaConversions()
    {
    }
}
