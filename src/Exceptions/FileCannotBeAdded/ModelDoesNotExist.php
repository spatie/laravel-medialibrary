<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

class ModelDoesNotExist extends FileCannotBeAdded
{
    public static function create(Model $model)
    {
        $modelClass = get_class($model);

        return new static("Before adding media to it, you should first save the {$modelClass}-model");
    }
}
