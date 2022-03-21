<?php

namespace Spatie\MediaLibrary\Tests\Support\PathGenerator;

use Illuminate\Config\Repository;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;

class BasePathGeneratorTest extends TestCase
{
    protected Repository $config;

    protected Media $media;

    protected Conversion $conversion;

    protected DefaultUrlGenerator $urlGenerator;

    protected CustomPathGenerator $pathGenerator;

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

    public function it_can_use_a_custom_path_generator_on_the_model()
    {
        config()->set('media-library.custom_path_generators', [
            TestModelWithConversion::class => CustomPathGenerator::class,
        ]);

        $media = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->toMediaCollection();

        $this->assertEquals($media->getUrl(), '/media/c4ca4238a0b923820dcc509a6f75849b/test.jpg');
    }
}
