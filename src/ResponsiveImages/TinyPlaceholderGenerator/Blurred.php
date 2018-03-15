<?php

namespace Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator;

use Spatie\Image\Image;

class Blurred implements TinyPlaceholderGenerator
{
    public function generateTinyPlaceholder(string $sourceImagePath, string $tinyImageDestinationPath)
    {
        $sourceImage = Image::load($sourceImagePath);

        $sourceImage->width(32)->blur(5)->save($tinyImageDestinationPath);
    }
}
