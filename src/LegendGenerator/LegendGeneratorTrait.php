<?php

namespace Spatie\MediaLibrary\LegendGenerator;

use Illuminate\Support\Arr;

trait LegendGeneratorTrait
{
    /**
     * Get the constraints legend string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
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
     */
    public function mimeTypesLegend(string $collectionName): string
    {
        $mediaConversions = $this->getMediaConversions($collectionName);
        if (empty($mediaConversions)) {
            return '';
        }
        $mediaCollection = $this->getMediaCollection($collectionName);
        $legendString = '';

        if (! empty($mediaCollection->acceptsMimeTypes)) {
            $extensions = $this->extensionsFromMimeTypes($mediaCollection->acceptsMimeTypes);
            $extensionsString = implode(',', $extensions);
            $extensionsString = str_replace(',', ', ', $extensionsString);
            $legendString .= trans_choice('medialibrary::medialibrary.constraint.types', count($extensions), [
                'types' => $extensionsString,
            ]);
        }

        return $legendString;
    }
}
