<?php

namespace Spatie\Medialibrary\Tests\Unit\UrlGenerator;

use Spatie\Medialibrary\Features\Conversions\ConversionCollection;
use Spatie\Medialibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\Medialibrary\Tests\TestCase;
use Spatie\Medialibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Spatie\Medialibrary\Support\UrlGenerator\LocalUrlGenerator;

class BaseUrlGeneratorTest extends TestCase
{
    protected $config;

    /**
     * @var \Spatie\Medialibrary\Media
     */
    protected \Spatie\Medialibrary\Features\MediaCollections\Models\Media $media;

    /**
     * @var \Spatie\Medialibrary\Features\Conversions\Conversion
     */
    protected \Spatie\Medialibrary\Features\Conversions\Conversion $conversion;

    /**
     * @var \Spatie\Medialibrary\Features\Conversions\Conversion
     */
    protected \Spatie\Medialibrary\Features\Conversions\Conversion $conversionKeepingOriginalImageFormat;

    /**
     * @var DefaultUrlGenerator
     */
    protected \Spatie\Medialibrary\Support\UrlGenerator\DefaultUrlGenerator $urlGenerator;

    /**
     * @var DefaultPathGenerator
     */
    protected \Spatie\Medialibrary\Support\PathGenerator\DefaultPathGenerator $pathGenerator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = app('config');

        $this->media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

        $this->conversion = ConversionCollection::createForMedia($this->media)->getByName('thumb');

        $this->conversionKeepingOriginalImageFormat = ConversionCollection::createForMedia($this->media)->getByName('keep_original_format');

        $this->urlGenerator = new DefaultUrlGenerator($this->config);
        $this->pathGenerator = new DefaultPathGenerator();

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
        $this->config->set('filesystems.disks.public.url', 'http://localhost/media/');

        $this->assertEquals('http://localhost/media/1/responsive-images/', $this->urlGenerator->getResponsiveImagesDirectoryUrl());
    }
}
