<?php

namespace Spatie\Medialibrary\Tests\Support\TestModels;

use Illuminate\Database\Eloquent\Model;
use Spatie\Medialibrary\HasMedia\HasMedia;
use Spatie\Medialibrary\HasMedia\HasMediaTrait;

class TestModelWithoutMediaConversions extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;
}
