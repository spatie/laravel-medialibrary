<?php

namespace Programic\MediaLibrary\Tests\TestSupport\TestModels;

use Illuminate\Database\Eloquent\Model;
use Programic\MediaLibrary\HasMedia;
use Programic\MediaLibrary\InteractsWithMedia;

class TestModelWithoutMediaConversions extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'test_models';

    protected $guarded = [];

    public $timestamps = false;
}
