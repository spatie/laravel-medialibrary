<?php

namespace Spatie\MediaLibrary\Support\MediaAttributes;

use ReflectionClass;
use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Attributes\MediaConversion;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidMediaAttribute;
use Spatie\MediaLibrary\MediaCollections\MediaCollection as MediaCollectionBuilder;

class MediaAttributeResolver
{
    /** @var array<class-string, array{collections: MediaCollection[], conversions: MediaConversion[]}> */
    private static array $cache = [];

    public function __construct(
        protected string $modelClass,
    ) {}

    /** @return MediaCollection[] */
    public function collectionAttributes(): array
    {
        return $this->parse()['collections'];
    }

    /** @return array<string, MediaCollectionBuilder> */
    public function toMediaCollections(): array
    {
        $collections = [];

        foreach ($this->collectionAttributes() as $attribute) {
            $builder = MediaCollectionBuilder::create($attribute->name);

            if ($attribute->onlyKeepLatest !== null) {
                $builder->onlyKeepLatest($attribute->onlyKeepLatest);
            } elseif ($attribute->singleFile) {
                $builder->singleFile();
            }

            if ($attribute->acceptsMimeTypes !== []) {
                $builder->acceptsMimeTypes($attribute->acceptsMimeTypes);
            }

            if ($attribute->disk !== null) {
                $builder->useDisk($attribute->disk);
            }

            if ($attribute->conversionsDisk !== null) {
                $builder->storeConversionsOnDisk($attribute->conversionsDisk);
            }

            if ($attribute->fallbackUrl !== null) {
                $builder->useFallbackUrl($attribute->fallbackUrl);
            }

            if ($attribute->fallbackPath !== null) {
                $builder->useFallbackPath($attribute->fallbackPath);
            }

            if ($attribute->responsiveImages) {
                $builder->withResponsiveImages();
            }

            $collections[$attribute->name] = $builder;
        }

        return $collections;
    }

    /** @return MediaConversion[] */
    public function conversionAttributes(): array
    {
        return $this->parse()['conversions'];
    }

    /** @return array{collections: MediaCollection[], conversions: MediaConversion[]} */
    protected function parse(): array
    {
        if (isset(self::$cache[$this->modelClass])) {
            return self::$cache[$this->modelClass];
        }

        $reflection = new ReflectionClass($this->modelClass);

        $collections = array_map(
            fn ($attribute) => $attribute->newInstance(),
            $reflection->getAttributes(MediaCollection::class),
        );

        $conversions = array_map(
            fn ($attribute) => $attribute->newInstance(),
            $reflection->getAttributes(MediaConversion::class),
        );

        $this->guardAgainstDuplicateCollections($collections);

        return self::$cache[$this->modelClass] = [
            'collections' => $collections,
            'conversions' => $conversions,
        ];
    }

    /** @param MediaCollection[] $collections */
    protected function guardAgainstDuplicateCollections(array $collections): void
    {
        $seen = [];

        foreach ($collections as $collection) {
            if (in_array($collection->name, $seen, true)) {
                throw InvalidMediaAttribute::duplicateCollection($collection->name, $this->modelClass);
            }

            $seen[] = $collection->name;
        }
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
