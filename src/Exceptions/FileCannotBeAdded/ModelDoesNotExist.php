<?php

namespace Spatie\Medialibrary\Exceptions\FileCannotBeAdded;

use Illuminate\Database\Eloquent\Model;
use Spatie\Medialibrary\Exceptions\FileCannotBeAdded\FileCannotBeAdded;

class ModelDoesNotExist extends FileCannotBeAdded
{
    public static function create(Model $model): self
    {
        $modelClass = get_class($model);

        return new static("Before adding media to it, you should first save the {$modelClass}-model");
    }
}
