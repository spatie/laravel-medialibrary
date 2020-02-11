<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;

class HasConversionTest extends TestCase
{
    /** @test */
    public function test()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertTrue($media->hasGeneratedConversion('thumb'));
    }
}
