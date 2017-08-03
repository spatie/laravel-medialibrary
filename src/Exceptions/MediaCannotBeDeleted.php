<?php

namespace Spatie\MediaLibrary\Exceptions;

use Exception;
use Spatie\MediaLibrary\Media;
use Illuminate\Database\Eloquent\Model;

class MediaCannotBeDeleted extends Exception
{
    public static function doesNotBelongToModel($mediaId, Model $model)
    {
        $modelClass = get_class($model);

        return new static("Media with id `{$mediaId}` cannot be deleted because it does not exist or does not belong to model {$modelClass} with id {$model->id}");
    }
}
