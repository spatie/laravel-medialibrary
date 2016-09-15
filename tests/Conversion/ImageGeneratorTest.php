<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use Spatie\MediaLibrary\ImageGenerator\ImageGenerator;
use Spatie\MediaLibrary\ImageGenerator\ImageGeneratorHandler;
use Spatie\MediaLibrary\ImageGenerator\FileTypes\Image;
use Spatie\MediaLibrary\ImageGenerator\FileTypes\Pdf;
use Spatie\MediaLibrary\ImageGenerator\FileTypes\Svg;
use Spatie\MediaLibrary\ImageGenerator\FileTypes\Video;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Test\TestCase;

class ImageGeneratorTest extends TestCase
{
    protected $conversionName = 'test';

    /**
     * @var \Spatie\MediaLibrary\Conversion\Conversion
     */
    protected $conversion;

    public function setUp()
    {
        $this->conversion = new Conversion($this->conversionName);

        parent::setUp();
    }

    /** @test */
    public function it_has_the_required_drivers()
    {
        $imageGenerators = (new Media())->getImageGenerators();

        $this->assertContains(Image::class, $imageGenerators);
        $this->assertContains(Pdf::class, $imageGenerators);
        $this->assertContains(Svg::class, $imageGenerators);
        $this->assertContains(Video::class, $imageGenerators);
    }

    /** @test */
    public function it_instantiate_the_required_drivers()
    {
        $mediaModelDrivers = (new Media())->getImageGenerators();
        $instanciatedDrivers = app(ImageGeneratorHandler::class)->getImageGenerators();

        $this->assertEquals($mediaModelDrivers->count(), $instanciatedDrivers->count());

        foreach ($instanciatedDrivers as $key => $driver) {
            $this->assertTrue($mediaModelDrivers->contains(get_class($driver)));
            $this->assertEquals($driver->getMediaType(), $key);
        }
    }

    /** @test */
    public function it_implements_the_before_conversion_driver_interface()
    {
        $instanciatedDrivers = app(ImageGeneratorHandler::class)->getImageGenerators();

        foreach ($instanciatedDrivers as $driver) {
            $this->assertContains(ImageGenerator::class, class_implements($driver));
        }
    }

    /**
     * @test
     * @dataProvider extensionProvider
     */
    public function it_can_detect_media_type_from_extension_with_drivers($extension, $type)
    {
        $media = new Media();
        $media->file_name = 'test.'.$extension;
        $this->assertEquals($type, $media->type_from_extension);
    }

    public static function extensionProvider()
    {
        $extensions =
            [
                ['jpg', (new Image())->getMediaType()],
                ['jpeg', (new Image())->getMediaType()],
                ['png', (new Image())->getMediaType()],
                ['gif', (new Image())->getMediaType()],
                ['webm', (new Video())->getMediaType()],
                ['mov', (new Video())->getMediaType()],
                ['mp4', (new Video())->getMediaType()],
                ['pdf', (new Pdf())->getMediaType()],
                ['svg', (new Svg())->getMediaType()],
                ['bla', Media::TYPE_OTHER],
            ];

        $capitalizedExtensions = array_map(function ($extension) {
            $extension[0] = strtoupper($extension[0]);

            return $extension;
        }, $extensions);

        return array_merge($extensions, $capitalizedExtensions);
    }

    /**
     * @test
     * @dataProvider mimeProvider
     *
     * @param string $file
     * @param string $type
     */
    public function it_can_determine_the_type_from_the_mime($file, $type)
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory($file))->toMediaLibrary();
        $this->assertEquals($type, $media->type_from_mime);
    }

    public static function mimeProvider()
    {
        return [
            ['image', (new Image())->getMediaType()],
            ['test.jpg', (new Image())->getMediaType()],
            ['test.webm', (new Video())->getMediaType()],
            ['test.mp4', (new Video())->getMediaType()],
            ['test.pdf', (new Pdf())->getMediaType()],
            ['test.svg', (new Svg())->getMediaType()],
            ['test', Media::TYPE_OTHER],
            ['test.txt', Media::TYPE_OTHER],
        ];
    }

    /** @test */
    public function image_driver_can_convert_image()
    {
        $imageFile = (new Image())->convertToImage($this->getTestJpg(), $this->conversion);

        $this->assertEquals('image/jpeg', mime_content_type($imageFile));
        $this->assertEquals($this->getTestJpg(), $imageFile);
    }

    /** @test */
    public function it_has_a_working_video_driver()
    {
        $driver = new Video();

        if (! $driver->hasRequirements()) {
            return;
        }

        $imageFile = $driver->convertToImage($this->getTestWebm(), $this->conversion);

        $this->assertEquals(str_replace('.webm', '.jpg', $this->getTestWebm()), $imageFile);
        $this->assertEquals('image/jpeg', mime_content_type($imageFile));
    }

    /** @test */
    public function it_has_a_working_pdf_driver()
    {
        $driver = new Pdf();

        if (! $driver->hasRequirements()) {
            return;
        }

        $imageFile = $driver->convertToImage($this->getTestPdf(), $this->conversion);

        $this->assertEquals(str_replace('.pdf', '.jpg', $this->getTestPdf()), $imageFile);
        $this->assertEquals('image/jpeg', mime_content_type($imageFile));
    }

    /** @test */
    public function it_has_a_working_svg_driver()
    {
        $driver = new Svg();

        if (! $driver->hasRequirements()) {
            return;
        }

        $imageFile = $driver->convertToImage($this->getTestSvg(), $this->conversion);

        $this->assertEquals(str_replace('.svg', '.png', $this->getTestSvg()), $imageFile);
        $this->assertEquals('image/png', mime_content_type($imageFile));
    }
}
