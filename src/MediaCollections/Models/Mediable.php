<?php

namespace Programic\MediaLibrary\MediaCollections\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Mediable extends Pivot
{
    use HasTimestamps;

    protected $table = 'mediables';
}
