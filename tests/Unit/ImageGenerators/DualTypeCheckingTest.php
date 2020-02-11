<?php

namespace Spatie\Medialibrary\Tests\Unit\ImageGenerators;

use Spatie\Medialibrary\Models\Media;
use Spatie\Medialibrary\Tests\TestCase;
use Spatie\Medialibrary\Tests\Support\TestImageGenerator;

class DualTypeCheckingTest extends TestCase
{
    /** @test */
    public function it_can_convert_an_image_with_a_valid_extension_and_mime_type()
    {
        $generator = new TestImageGenerator();
        $generator->shouldMatchBothExtensionsAndMimetypes = true;

        $generator->supportedMimetypes->push('supported-mime-type');
        $generator->supportedExtensions->push('supported-extension');

        $media = new Media();
        $media->mime_type = 'supported-mime-type';
        $media->file_name = 'some-file.supported-extension';

        $this->assertTrue($generator->canConvert($media));
    }

    /** @test */
    public function it_cannot_convert_an_image_with_an_invalid_extension_and_mime_type()
    {
        $generator = new TestImageGenerator();
        $generator->shouldMatchBothExtensionsAndMimetypes = true;

        $generator->supportedMimetypes->push('supported-mime-type');
        $generator->supportedExtensions->push('supported-extension');

        $media = new Media();
        $media->mime_type = 'invalid-mime-type';
        $media->file_name = 'some-file.invalid-extension';

        $this->assertFalse($generator->canConvert($media));
    }

    /** @test */
    public function it_cannot_convert_an_image_with_only_a_valid_mime_type()
    {
        $generator = new TestImageGenerator();
        $generator->shouldMatchBothExtensionsAndMimetypes = true;

        $generator->supportedMimetypes->push('supported-mime-type');
        $generator->supportedExtensions->push('supported-extension');

        $media = new Media();
        $media->mime_type = 'supported-mime-type';
        $media->file_name = 'some-file.invalid-extension';

        $this->assertFalse($generator->canConvert($media));
    }

    /** @test */
    public function it_cannot_convert_an_image_with_only_a_valid_extension()
    {
        $generator = new TestImageGenerator();
        $generator->shouldMatchBothExtensionsAndMimetypes = true;

        $generator->supportedExtensions->push('supported-extension');
        $generator->supportedMimetypes->push('supported-mime-type');

        $media = new Media();
        $media->mime_type = 'invalid-mime-type';
        $media->file_name = 'some-file.supported-extension';

        $this->assertFalse($generator->canConvert($media));
    }
}
