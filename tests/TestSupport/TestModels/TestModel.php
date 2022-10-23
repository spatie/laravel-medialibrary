<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
            ->useFallbackUrl('/default-url.jpg')
            ->useFallbackUrl('/default-avatar-thumb-url.jpg', 'avatar_thumb')
            ->useFallbackPath('/default-path.jpg')
            ->useFallbackPath('/default-avatar-thumb-path.jpg', 'avatar_thumb');
    }
}
