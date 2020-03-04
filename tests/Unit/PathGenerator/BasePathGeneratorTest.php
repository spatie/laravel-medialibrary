<?php

namespace Spatie\Medialibrary\Tests\Unit\PathGenerator;

use Spatie\Medialibrary\Conversions\ConversionCollection;
use Spatie\Medialibrary\Tests\TestCase;
use Spatie\Medialibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Spatie\Medialibrary\Support\UrlGenerator\LocalUrlGenerator;

class BasePathGeneratorTest extends TestCase
{
    protected $config;

    /**
     * @var \Spatie\Medialibrary\Media
     */
    protected $media;

    /**
     * @var \Spatie\Medialibrary\Conversions\Conversion
     */
    protected \Spatie\Medialibrary\Conversions\Conversion $conversion;

    /**
     * @var \Spatie\Medialibrary\Support\UrlGenerator\DefaultUrlGenerator
     */
    protected \Spatie\Medialibrary\Support\UrlGenerator\DefaultUrlGenerator $urlGenerator;

    /**
     * @var \Spatie\Medialibrary\Support\PathGenerator\DefaultPathGenerator
     */
    protected \Spatie\Medialibrary\Tests\Unit\PathGenerator\CustomPathGenerator $pathGenerator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = app('config');

        $this->urlGenerator = new DefaultUrlGenerator($this->config);

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
