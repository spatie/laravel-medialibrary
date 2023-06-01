<?php

namespace Programic\MediaLibrary\MediaCollections\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Mediable extends Pivot
{
    protected $table = 'mediables';
}
