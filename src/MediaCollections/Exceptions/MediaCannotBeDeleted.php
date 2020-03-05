<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class MediaCannotBeDeleted extends Exception
{
    public static function doesNotBelongToModel($mediaId, Model $model): self
    {
        $modelClass = get_class($model);

        return new static("Media with id `{$mediaId}` cannot be deleted because it does not exist or does not belong to model {$modelClass} with id {$model->getKey()}");
    }
}
