<?php

namespace Spatie\MediaLibrary\Test\UrlGenerator;

use Spatie\MediaLibrary\Conversion\ConversionCollectionFactory;
use Spatie\MediaLibrary\Test\TestCase;
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
     * @var LocalUrlGenerator
     */
    protected $generator;

    public function setUp()
    {
        parent::setUp();

        $this->config = app('config');

        $this->media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaLibrary();

        $this->conversion = ConversionCollectionFactory::createForMedia($this->media)->getByName('thumb');

        // because BaseUrlGenerator is abstract we'll use LocalUrlGenerator to test the methods of base
        $this->generator = new LocalUrlGenerator($this->config);

        $this->generator
            ->setMedia($this->media)
            ->setConversion($this->conversion);
    }

    /**
     * @test
     */
    public function it_can_get_the_path_relative_to_the_root_of_media_folder()
    {
        $pathRelativeToRoot = $this->media->id.'/conversions/'.$this->conversion->getName().'.'.$this->conversion->getResultExtension($this->media->extension);

        $this->assertEquals($pathRelativeToRoot, $this->generator->getPathRelativeToRoot());
    }
}
