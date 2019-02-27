<?php

namespace Spatie\MediaLibrary\Tests\Unit\PathGenerator;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\UrlGenerator\LocalUrlGenerator;
use Spatie\MediaLibrary\Conversion\ConversionCollection;

class BasePathGeneratorTest extends TestCase
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
     * @var \Spatie\MediaLibrary\UrlGenerator\LocalUrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var \Spatie\MediaLibrary\PathGenerator\BasePathGenerator
     */
    protected $pathGenerator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = app('config');

        // BaseUrlGenerator is abstract so we'll use LocalUrlGenerator to test the methods of base
        $this->urlGenerator = new LocalUrlGenerator($this->config);

        $this->pathGenerator = new CustomPathGenerator();

        $this->urlGenerator->setPathGenerator($this->pathGenerator);
    }

    /** @test */
    public function it_can_get_the_custom_path_for_media_without_conversions()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();

        $this->urlGenerator->setMedia($media);

        $pathRelativeToRoot = md5($media->id).'/'.$media->file_name;

        $this->assertEquals($pathRelativeToRoot, $this->urlGenerator->getPathRelativeToRoot());
    }

    /** @test */
    public function it_can_get_the_custom_path_for_media_with_conversions()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();
        $conversion = ConversionCollection::createForMedia($media)->getByName('thumb');

        $this->urlGenerator
            ->setMedia($media)
            ->setConversion($conversion);

        $pathRelativeToRoot = md5($media->id).'/c/test-'.$conversion->getName().'.'.$conversion->getResultExtension($media->extension);

        $this->assertEquals($pathRelativeToRoot, $this->urlGenerator->getPathRelativeToRoot());
    }
}
