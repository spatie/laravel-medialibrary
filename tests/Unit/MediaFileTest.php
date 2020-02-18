<?php

namespace Spatie\MediaLibrary\Tests\Unit;

use Spatie\Medialibrary\Tests\TestCase;

class MediaFileTest extends TestCase
{
    /** @test */
    public function it()
    {
        $testJpgPath = $this->getTestJpg();

        $expectedFileSize = filesize($testJpgPath);

        $media = $this->testModelWithoutMediaConversions
            ->addMedia($testJpgPath)
            ->toMediaCollection();

        $file = $media->getFile();

        $this->assertEquals('test.jpg', $file->path);
        $this->assertEquals('test', $file->name);
        $this->assertEquals('jpg', $file->extension);
        $this->assertEquals('image/jpeg', $file->mimeType);
        $this->assertEquals($expectedFileSize, $file->size);
    }
}
