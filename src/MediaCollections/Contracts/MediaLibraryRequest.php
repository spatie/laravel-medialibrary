<?php

namespace Spatie\MediaLibrary\MediaCollections\Contracts;

use Illuminate\Support\Collection;
use Spatie\MediaLibraryPro\Dto\MediaLibraryRequestHandler;

interface MediaLibraryRequest
{
    public function mediaLibraryRequestItems(string $key): Collection;
}
