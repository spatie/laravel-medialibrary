<?php

namespace Spatie\Medialibrary\Exceptions;

use Illuminate\Database\Eloquent\Model;

class MediaCannotBeDeleted
{
    public static function doesNotBelongToModel(Media $media, Model $model)
    {
        $modelClass = get_class($model);
        
        return new static("Media with id {$media->id} cannot be deleted because it does not belong to model {$modelClass} with id {$model->id}");
    }
}