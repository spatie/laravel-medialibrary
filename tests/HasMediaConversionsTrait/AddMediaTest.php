<?php

namespace Spatie\MediaLibrary\Test\HasMediaWithoutConversionsTrait;

use Spatie\MediaLibrary\Test\TestCase;

class AddMediaTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_add_an_file_to_the_default_collection()
    {
        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestFilesDirectory('test.jpg'));

        $this->assertEquals('default', $media->collection_name);
    }
}
