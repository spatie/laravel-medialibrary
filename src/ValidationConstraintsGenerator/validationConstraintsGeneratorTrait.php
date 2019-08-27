<?php

namespace Spatie\MediaLibrary\ValidationConstraintsGenerator;

use Illuminate\Support\Arr;
use Spatie\MediaLibrary\Exceptions\CollectionNotFound;

trait validationConstraintsGeneratorTrait
{
    /**
     * Get the constraints validation string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     * @throws \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     */
    public function validationConstraints(string $collectionName): string
    {
        $dimensions = $this->dimensionValidationConstraints($collectionName);
        $mimeTypes = $this->mimeTypesValidationConstraints($collectionName);
        $separator = $dimensions && $mimeTypes ? '|' : '';

        return ($dimensions ? $dimensions . $separator : '') . ($mimeTypes);
    }

    /**
     * Get a collection dimension validation constraints string from its name name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     * @throws \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     */
    public function dimensionValidationConstraints(string $collectionName): string
    {
        /** @var \Spatie\MediaLibrary\HasMedia\HasMediaTrait $this */
        $maxSizes = $this->collectionMaxSizes($collectionName);
        if (empty($maxSizes)) {
            return '';
        }
        $width = $maxSizes['width'] ? 'min_width=' . $maxSizes['width'] : '';
        $height = $maxSizes['height'] ? 'min_height=' . $maxSizes['height'] : '';
        $separator = $width && $height ? ',' : '';

        return $width || $height ? 'dimensions:' . $width . $separator . $height : '';
    }

    /**
     * Get a collection mime types constraints validation string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     */
    public function mimeTypesValidationConstraints(string $collectionName): string
    {
        /** @var \Spatie\MediaLibrary\HasMedia\HasMediaTrait $this */
        $this->registerMediaCollections();
        /** @var \Spatie\MediaLibrary\HasMedia\HasMediaTrait $this */
        $collection = head(Arr::where($this->mediaCollections, function ($collection) use ($collectionName) {
            return $collection->name === $collectionName;
        }));
        if (! $collection) {
            /** @var \Illuminate\Database\Eloquent\Model $this */
            throw CollectionNotFound::notDeclaredInModel($this, $collectionName);
        }
        $validationString = '';
        if (! empty($collection->acceptsMimeTypes)) {
            $validationString .= 'mimetypes:' . implode(',', $collection->acceptsMimeTypes);
        }

        return $validationString;
    }
}
