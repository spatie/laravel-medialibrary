<?php

namespace Spatie\MediaLibrary\Tests;

use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Spatie\MediaLibrary\Media;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class TestModel extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * Register the conversions that should be performed.
     *
     * @return array
     */
    public function registerMediaConversions(Media $media = null)
    {
    }
}
