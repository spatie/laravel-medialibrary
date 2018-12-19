<?php

namespace Spatie\MediaLibrary\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class ConversionsNotFound extends Exception
{
    public static function noneDeclaredInModel(Model $model)
    {
        $modelClass = get_class($model);

        return new static("No conversion declared in the {$modelClass}-model");
    }
}
