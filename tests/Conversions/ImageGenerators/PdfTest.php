<?php

namespace Spatie\MediaLibrary\Tests\Conversions\ImageGenerators;

use Spatie\MediaLibrary\Conversions\ImageGenerators\Pdf;
use Spatie\MediaLibrary\Tests\TestCase;

class PdfTest extends TestCase
{
    /** @test */
    public function it_can_convert_a_pdf()
    {
        $imageGenerator = new Pdf();

        if (! $imageGenerator->requirementsAreInstalled()) {
            $this->markTestSkipped('Skipping pdf test because requirements to run it are not met');
        }

        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestPdf())->toMediaCollection();

        $this->assertTrue($imageGenerator->canConvert($media));

        $imageFile = $imageGenerator->convert($media->getPath());

        $this->assertEquals('image/jpeg', mime_content_type($imageFile));
    }
}
