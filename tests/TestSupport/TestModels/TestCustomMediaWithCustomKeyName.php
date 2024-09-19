<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestCustomMediaWithCustomKeyName extends Media
{
    protected $table = 'media';

    protected $primaryKey = 'custom_key_id';
}
