<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use Spatie\MediaLibrary\Test\TestCase;

class VideoThumbnailTest extends TestCase
{
    /** @test */
    public function it_can_create_a_video_thumbnail()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestWebm())->toMediaLibrary();

        $conversionName = 'thumb';

        $this->assertEquals("/media/{$media->id}/conversions/{$conversionName}.jpg", $media->getUrl($conversionName));
    }
}
