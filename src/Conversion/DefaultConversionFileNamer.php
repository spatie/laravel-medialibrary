<?php

namespace Spatie\Medialibrary\Conversion;

use Spatie\Medialibrary\Models\Media;

class DefaultConversionFileNamer implements ConversionFileNamer
{
    public function getName(Conversion $conversion, Media $media): string
    {
        $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);
        
        $extension = $this->getExtension($conversion, $media);

        return "{$fileName}-{$conversion->getName()}.{$extension}";
    }
    
    public function getExtension(Conversion $conversion, Media $media): string
    {
        $fileExtension = pathinfo($media->file_name, PATHINFO_EXTENSION);
        
        return $conversion->getResultExtension($fileExtension) ?: $fileExtension;
    }
}
