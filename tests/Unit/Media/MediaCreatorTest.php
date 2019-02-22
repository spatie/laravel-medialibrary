<?php

namespace Tests\Unit\Media;

use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;

class MediaCreatorTest extends TestCase
{
    public function testCreateMedia()
    {
        $this->assertTrue(true);

        Media::create([
                'model_type' => 'model',
                'model_id' => 1,
                'file_name' => 'file.jpg',
                'mime_type' => 'image/jpeg',
                'custom_properties' => [],
                'size' => '1000',
                'disk' => 'local',
                'responsive_images' => [],
                'manipulations' => [],
                'name' => 'media_jpeg',
                'collection_name' => 'client_files',
        ]);
    }
}
