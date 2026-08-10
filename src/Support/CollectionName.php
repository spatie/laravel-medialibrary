<?php

namespace Spatie\MediaLibrary\Support;

use BackedEnum;

class CollectionName
{
    public static function resolve(BackedEnum|string $collectionName): string
    {
        return $collectionName instanceof BackedEnum
            ? (string) $collectionName->value
            : $collectionName;
    }

    /**
     * @param  array<int, BackedEnum|string>  $collectionNames
     * @return array<int, string>
     */
    public static function resolveMany(array $collectionNames): array
    {
        return array_map(
            static fn (BackedEnum|string $collectionName) => static::resolve($collectionName),
            $collectionNames,
        );
    }
}
