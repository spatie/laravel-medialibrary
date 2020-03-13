<?php

namespace Spatie\MediaLibrary\Support;

use Spatie\MediaLibrary\MediaCollections\Exceptions\FunctionalityNotAvailable;
use Spatie\MediaLibraryPro\Models\TemporaryUpload;

class MediaLibraryPro
{
    public static function ensureInstalled()
    {
        if (! class_exists(TemporaryUpload::class)) {
            throw FunctionalityNotAvailable::mediaLibraryProRequired();
        }
    }

    public static function isInstalled(): bool
    {
        return class_exists(TemporaryUpload::class);
    }
}
