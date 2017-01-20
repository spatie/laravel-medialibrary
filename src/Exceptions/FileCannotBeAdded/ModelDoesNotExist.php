<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Exception;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Database\Eloquent\Model;

class ModelDoesNotExist extends FileCannotBeAdded
{
    public static function create(Model $model)
    {
        $modelClass = get_class($model);

        return new static("Before adding media to it, you should first save the {$modelClass}-model");
    }
}