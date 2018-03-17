<?php

namespace Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator;

interface TinyPlaceholderGenerator
{
    /**
     * This function should generate a tiny jpg representation of the image
     * given in $sourceImage. The tiny jpg should be saved at $tinyImageDestination.
     */
    public function generateTinyPlaceholder(string $sourceImage, string $tinyImageDestination);
}
