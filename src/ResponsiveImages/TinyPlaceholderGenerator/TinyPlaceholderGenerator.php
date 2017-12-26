<?php

namespace Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator;

interface TinyPlaceholderGenerator
{
    public function generateTinyPlaceholder(string $sourceImage, string $tinyImageDestination);
}
