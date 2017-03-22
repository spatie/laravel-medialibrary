<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Test\TestCase;

class GetTypeTest extends TestCase
{
    /** @test */
    public function it_can_return_the_file_mime()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertEquals('image/jpeg', $media->mime_type);
    }
}
