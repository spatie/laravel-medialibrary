<?php

namespace Spatie\MediaLibrary\Tests\TestSupport;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestMediaModel extends Media
{
    public function getDownloadFilename(): string
    {
        return 'overriden_testing.jpg';
    }
}
