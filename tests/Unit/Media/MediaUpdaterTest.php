<?php

namespace Tests\Unit\Media;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class MediaUpdaterTest extends TestCase
{
    public function testExample()
    {
        $this->assertTrue(true);

        Storage::fake('local');

        $file = UploadedFile::fake()->image('file.jpg');

        $media = Media::create([
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

        Storage::disk('local')->putFileAs('1', $file, 'file.jpg');

        $media->update([
            'mime_type' => 'image/png',
            'custom_properties' => [],
            'size' => '1500',
            'disk' => 'local',
            'responsive_images' => [],
            'manipulations' => [],
            'name' => 'media_png',
            'collection_name' => 'organizer_files',
        ]);
    }
}
