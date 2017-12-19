<?php

namespace Spatie\MediaLibrary\Test;

use Spatie\MediaLibrary\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;

class TestCustomModelWithSoftDeletes extends Media implements HasMediaConversions
{
    use HasMediaTrait, SoftDeletes;

    protected $table = 'media';
    protected $guarded = [];
    public $timestamps = false;
    protected $dates = ['deleted_at'];

    public function registerMediaConversions(Media $media = null)
    {
    }
}
