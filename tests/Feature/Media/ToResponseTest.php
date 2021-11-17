<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ToResponseTest extends TestCase
{
    /** @test */
    public function test_to_response_sends_the_content()
    {
        $media = $this->testModel->addMedia($testPdf = $this->getTestPdf())->preservingOriginal()->toMediaCollection();

        ob_start();
        @$media->toResponse(request())->sendContent();
        $content = ob_get_contents();
        ob_end_clean();

        $temporaryDirectory = (new TemporaryDirectory())->create();
        file_put_contents($temporaryDirectory->path('response.pdf'), $content);

        $this->assertFileEquals($testPdf, $temporaryDirectory->path('response.pdf'));
    }

    /** @test */
    public function test_to_response_sends_correct_attachment_header()
    {
        $media = $this->testModel->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection();

        $response = $media->toResponse(request());

        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function test_to_inline_response_sends_correct_attachment_header()
    {
        $media = $this->testModel->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection();

        $response = $media->toInlineResponse(request());

        $this->assertEquals('inline; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }
}
