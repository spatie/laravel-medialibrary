<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            $column = self::determineUniqueIdentifierColumn();

            /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $model */
            if (empty($model->{$column})) {
                $model->{$column} = $column === 'ulid' ? (string) Str::ulid() : (string) Str::uuid();
            }
        });
    }

    public static function findByUuid(string $uuid): ?Model
    {
        return static::where('uuid', $uuid)->first();
    }

    public static function findByUlid(string $ulid): ?Model
    {
        return static::where('ulid', $ulid)->first();
    }

    public static function findBy(string $unique): ?Model
    {
        return static::where(self::determineUniqueIdentifierColumn(), $unique)->first();
    }
}
