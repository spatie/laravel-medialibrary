<?php

namespace Spatie\Medialibrary\Tests\Support\TestModels;

use Illuminate\Database\Eloquent\Model;
use Spatie\Medialibrary\HasMedia;
use Spatie\Medialibrary\InteractsWithMedia;

class TestModelWithoutMediaConversions extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;
}
