<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            /** @var Media $model */
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public static function findByUuid(string $uuid): ?Model
    {
        return static::where('uuid', $uuid)->first();
    }
}
