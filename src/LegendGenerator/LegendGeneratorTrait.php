<?php

namespace Spatie\MediaLibrary\LegendGenerator;

use Illuminate\Support\Arr;
use Spatie\MediaLibrary\Exceptions\CollectionNotFound;

trait LegendGeneratorTrait
{
    /**
     * Get the constraints legend string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     * @throws \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     */
    public function constraintsLegend(string $collectionName): string
    {
        $dimensionsLegend = $this->dimensionsLegend($collectionName);
        $mimeTypesLegend = $this->mimeTypesLegend($collectionName);
        $separator = $dimensionsLegend && $mimeTypesLegend ? ' ' : '';

        return ($dimensionsLegend ? $dimensionsLegend . $separator : '') . $mimeTypesLegend;
    }

    /**
     * Get a collection dimensions constraints legend string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     * @throws \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     */
    public function dimensionsLegend(string $collectionName): string
    {
        /** @var \Spatie\MediaLibrary\HasMedia\HasMediaTrait $this */
        $sizes = $this->collectionMaxSizes($collectionName);
        $width = Arr::get($sizes, 'width');
        $height = Arr::get($sizes, 'height');
        $legend = '';
        if ($width && $height) {
            $legend = (string) __('medialibrary::medialibrary.constraint.dimensions.both', [
                'width'  => $width,
                'height' => $height,
            ]);
        } elseif ($width && ! $height) {
            $legend = (string) __('medialibrary::medialibrary.constraint.dimensions.width', [
                'width' => $width,
            ]);
        } elseif (! $width && $height) {
            $legend = (string) __('medialibrary::medialibrary.constraint.dimensions.height', [
                'height' => $height,
            ]);
        }

        return $legend;
    }

    /**
     * Get a collection mime types constraints legend string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     */
    public function mimeTypesLegend(string $collectionName): string
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
        $legendString = '';
        if (! empty($collection->acceptsMimeTypes)) {
            $legendString .= __('medialibrary::medialibrary.constraint.mimeTypes', [
                'mimetypes' => implode(', ', $collection->acceptsMimeTypes),
            ]);
        }

        return $legendString;
    }
}
