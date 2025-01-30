<?php

namespace Programic\MediaLibrary\Tests\TestSupport\TestModels;

use Programic\MediaLibrary\MediaCollections\Models\Media;

class TestCustomMediaWithCustomKeyName extends Media
{
    protected $table = 'media';

    protected $primaryKey = 'custom_key_id';
}
