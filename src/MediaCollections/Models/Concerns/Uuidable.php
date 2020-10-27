<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait Uuidable
{
    public static function bootUuidable()
    {
        static::creating(function (Model $model) {
            if ($model::usesUuids() && empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public static function findByUuid(string $uuid): ?Model
    {
        if (static::usesUuids()) {
            return static::where('uuid', $uuid)->first();
        }

        return null;
    }

    protected static function usesUuids() {
        return config('media-library.uses_media_uuids', true);
    }
}
