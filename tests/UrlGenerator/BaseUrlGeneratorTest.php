<?php

namespace Spatie\MediaLibrary\Test\UrlGenerator;

use Spatie\MediaLibrary\Test\TestCase;
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
     * @var LocalUrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var BasePathGenerator
     */
    protected $pathGenerator;

    public function setUp()
    {
        parent::setUp();

        $this->config = app('config');

        $this->media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();

        $this->conversion = ConversionCollection::createForMedia($this->media)->getByName('thumb');

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
        $pathRelativeToRoot = $this->media->id.'/conversions/'.$this->conversion->getName().'.'.$this->conversion->getResultExtension($this->media->extension);

        $this->assertEquals($pathRelativeToRoot, $this->urlGenerator->getPathRelativeToRoot());
    }
}
