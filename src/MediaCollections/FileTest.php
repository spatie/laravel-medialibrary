<?php

namespace Spatie\MediaLibrary\MediaCollections;

use Spatie\MediaLibrary\Support\File;
use Spatie\MediaLibrary\Tests\TestCase;

class FileTest extends TestCase
{
    /** @test */
    public function it_can_determine_a_human_readable_filesize()
    {
        $this->assertEquals('10 B', File::getHumanReadableSize(10));
        $this->assertEquals('100 B', File::getHumanReadableSize(100));
        $this->assertEquals('1000 B', File::getHumanReadableSize(1000));
        $this->assertEquals('9.77 KB', File::getHumanReadableSize(10000));
        $this->assertEquals('976.56 KB', File::getHumanReadableSize(1000000));
        $this->assertEquals('9.54 MB', File::getHumanReadableSize(10000000));
        $this->assertEquals('9.31 GB', File::getHumanReadableSize(10000000000));
    }

    /** @test */
    public function it_can_determine_the_mime_type_of_a_file()
    {
        $this->assertEquals('text/x-php', File::getMimeType(__FILE__));
    }
}
