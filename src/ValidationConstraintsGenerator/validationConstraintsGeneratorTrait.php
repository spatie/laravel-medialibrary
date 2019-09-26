<?php

namespace Spatie\MediaLibrary\ValidationConstraintsGenerator;

use Spatie\MediaLibrary\Exceptions\CollectionNotFound;

trait validationConstraintsGeneratorTrait
{
    /**
     * Get the constraints validation string for a media collection.
     *
     * @param string $collectionName
     *
     * @return array
     */
    public function validationConstraints(string $collectionName): array
    {
        $mimeTypeConstraints = $this->mimeTypesValidationConstraints($collectionName);
        $mimesConstraints = $this->mimesValidationConstraints($collectionName);
        $dimensionConstraints = $this->dimensionValidationConstraints($collectionName);

        return array_values(array_filter([$mimeTypeConstraints, $mimesConstraints, $dimensionConstraints]));
    }

    /**
     * Get a collection mime types constraints validation string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function mimeTypesValidationConstraints(string $collectionName): string
    {
        $mediaConversions = $this->getMediaConversions($collectionName);
        if (empty($mediaConversions)) {
            return '';
        }
        $mediaCollection = $this->getMediaCollection($collectionName);
        $validationString = '';
        if (! empty($mediaCollection->acceptsMimeTypes)) {
            $validationString .= 'mimetypes:' . implode(',', $mediaCollection->acceptsMimeTypes);
        }

        return $validationString;
    }

    /**
     * Get a collection mime types constraints validation string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function mimesValidationConstraints(string $collectionName): string
    {
        $mediaConversions = $this->getMediaConversions($collectionName);
        if (empty($mediaConversions)) {
            return '';
        }
        $mediaCollection = $this->getMediaCollection($collectionName);
        $validationString = '';
        if (! empty($mediaCollection->acceptsMimeTypes)) {
            $acceptedExtensions = $this->extensionsFromMimeTypes($mediaCollection->acceptsMimeTypes);
            if (! empty($acceptedExtensions)) {
                $validationString .= 'mimes:' . implode(',', $acceptedExtensions);
            }
        }

        return $validationString;
    }

    /**
     * Get a collection dimension validation constraints string from its name.
     *
     * @param string $collectionName
     *
     * @return string
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
}
