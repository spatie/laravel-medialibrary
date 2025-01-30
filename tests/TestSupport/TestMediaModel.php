<?php

namespace Programic\MediaLibrary\Tests\TestSupport;

use Programic\MediaLibrary\MediaCollections\Models\Media;

class TestMediaModel extends Media
{
    public function getDownloadFilename(): string
    {
        return 'overriden_testing.jpg';
    }
}
