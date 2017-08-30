<?php

namespace Spatie\MediaLibrary\Test;

use Spatie\MediaLibrary\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;

class TestModelWithSoftDeletes extends Model implements HasMediaConversions
{
    use HasMediaTrait, SoftDeletes;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;

    public function registerMediaConversions(Media $media = null)
    {
    }
}
