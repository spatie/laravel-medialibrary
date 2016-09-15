<?php

namespace Spatie\MediaLibrary\Test\ImageGenerators;

use Spatie\MediaLibrary\ImageGenerator\FileTypes\Pdf;
use Spatie\MediaLibrary\Test\TestCase;

class PdfTest extends TestCase
{
    /** @test */
    public function it_can_convert_a_pdf()
    {
        $imageGenerator = new Pdf();

        if (! $imageGenerator->areRequirementsInstalled()) {
            $this->markTestSkipped('Skipping pdf test because requirements to run it are not met');
        }

        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestPdf())->toMediaLibrary();

        $this->assertTrue($imageGenerator->canConvert($media->getPath()));

        $imageFile = $imageGenerator->convert($media->getPath());

        $this->assertEquals('image/jpeg', mime_content_type($imageFile));

        //$this->assertEquals($imageFile, $media->getPath());
    }
}