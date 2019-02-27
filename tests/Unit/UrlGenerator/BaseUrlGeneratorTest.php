<?php

namespace Spatie\MediaLibrary\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\UrlGenerator\LocalUrlGenerator;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;

class BaseUrlGeneratorTest extends TestCase
{
    protected $config;

    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $media;

    /**
     * @var \Spatie\MediaLibrary\Conversion\Conversion
     */
    protected $conversion;

    /**
     * @var \Spatie\MediaLibrary\Conversion\Conversion
     */
    protected $conversionKeepingOriginalImageFormat;

    /**
     * @var LocalUrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var BasePathGenerator
     */
    protected $pathGenerator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = app('config');

        $this->media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

        $this->conversion = ConversionCollection::createForMedia($this->media)->getByName('thumb');

        $this->conversionKeepingOriginalImageFormat = ConversionCollection::createForMedia($this->media)->getByName('keep_original_format');

        // because BaseUrlGenerator is abstract we'll use LocalUrlGenerator to test the methods of base
        $this->urlGenerator = new LocalUrlGenerator($this->config);
        $this->pathGenerator = new BasePathGenerator();

        $this->urlGenerator
            ->setMedia($this->media)
            ->setConversion($this->conversion)
            ->setPathGenerator($this->pathGenerator);
    }

    /** @test */
    public function it_can_get_the_path_relative_to_the_root_of_media_folder()
    {
        $pathRelativeToRoot = $this->media->id.'/conversions/test-'.$this->conversion->getName().'.jpg';

        $this->assertEquals($pathRelativeToRoot, $this->urlGenerator->getPathRelativeToRoot());
    }

    /** @test */
    public function it_can_get_the_path_relative_to_the_root_of_media_folder_when_keeping_the_original_image_format()
    {
        $this->urlGenerator->setConversion($this->conversionKeepingOriginalImageFormat);

        $pathRelativeToRoot = $this->media->id
            .'/conversions/'.
            'test-'.$this->conversionKeepingOriginalImageFormat->getName()
            .'.png';

        $this->assertEquals($pathRelativeToRoot, $this->urlGenerator->getPathRelativeToRoot());
    }
}
