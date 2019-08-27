<?php

namespace Spatie\MediaLibrary\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class CollectionNotFound extends Exception
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $collectionName
     *
     * @return \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     */
    public static function notDeclaredInModel(Model $model, string $collectionName)
    {
        $modelClass = get_class($model);

        return new static("No collection `{$collectionName}` declared in the {$modelClass}-model");
    }
}
