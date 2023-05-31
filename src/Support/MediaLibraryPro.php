<?php

namespace Programic\MediaLibrary\Support;

use Programic\MediaLibrary\MediaCollections\Exceptions\FunctionalityNotAvailable;
use Programic\MediaLibraryPro\Models\TemporaryUpload;

class MediaLibraryPro
{
    public static function ensureInstalled(): void
    {
        if (! self::isInstalled()) {
            throw FunctionalityNotAvailable::mediaLibraryProRequired();
        }
    }

    public static function isInstalled(): bool
    {
        return class_exists(TemporaryUpload::class);
    }
}
