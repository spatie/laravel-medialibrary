<?php
namespace Spatie\MediaLibrary\Test;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\HasMediaTrait;
use Spatie\MediaLibrary\HasMediaWithoutConversions;

class TestModelWithoutMediaConversions extends Model implements HasMediaWithoutConversions
{
    use HasMediaTrait;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;


}
