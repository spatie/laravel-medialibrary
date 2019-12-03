<?php

namespace Spatie\MediaLibrary\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\UrlGenerator\LocalUrlGenerator;

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

    /** @test * */
    public function it_appends_a_version_string_when_versioning_is_enabled()
    {
        config()->set('medialibrary.version_urls', true);

        $url = '/media/'.$this->media->id.'/conversions/test-'.$this->conversion->getName().'.jpg?v='.$this->media->updated_at->timestamp;

        $this->assertEquals($url, $this->urlGenerator->getUrl());

        config()->set('medialibrary.version_urls', false);

        $url = '/media/'.$this->media->id.'/conversions/test-'.$this->conversion->getName().'.jpg';

        $this->assertEquals($url, $this->urlGenerator->getUrl());
    }

    /** @test */
    public function it_can_get_the_responsive_images_directory_url()
    {
        $this->config->set('filesystems.disks.public.url', 'https://localhost/media/');

        $this->assertEquals('https://localhost/media/1/responsive-images/', $this->urlGenerator->getResponsiveImagesDirectoryUrl());

        $this->config->set('filesystems.disks.public.url', null);

        $this->assertEquals('http://localhost/media/1/responsive-images/', $this->urlGenerator->getResponsiveImagesDirectoryUrl());
    }
}
