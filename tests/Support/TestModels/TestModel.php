<?php

namespace Spatie\Medialibrary\Tests\Support\TestModels;

use Illuminate\Database\Eloquent\Model;
use Spatie\Medialibrary\HasMedia;
use Spatie\Medialibrary\InteractsWithMedia;
use Spatie\Medialibrary\Features\MediaCollections\Models\Media;

class TestModel extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'test_models';

    protected $guarded = [];

    public $timestamps = false;

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatar')
            ->useFallbackUrl('/default.jpg')
            ->useFallbackPath('/default.jpg');
    }
}
