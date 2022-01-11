<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class MediaCannotBeDeleted extends Exception
{
    public static function doesNotBelongToModel($mediaId, Model $model): self
    {
        $modelClass = $model::class;

        return new static("Media with id `{$mediaId}` cannot be deleted because it does not exist or does not belong to model {$modelClass} with id {$model->getKey()}");
    }
}
