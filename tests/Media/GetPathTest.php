<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Test\TestCase;

class GetPathTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_get_a_path_of_an_original_item()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaLibrary();

        $this->assertEquals($media->getPath(), $this->getMediaDirectory()."/{$media->id}/test.jpg");
    }

    /**
     * @test
     */
    public function it_can_get_a_path_of_a_derived_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaLibrary();

        $conversionName = 'thumb';

        $this->assertEquals($this->getMediaDirectory()."/{$media->id}/conversions/{$conversionName}.jpg", $media->getPath($conversionName));
    }

    /**
     * @test
     */
    public function it_returns_an_exception_when_getting_a_path_for_an_unknown_conversion()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaLibrary();

        $this->setExpectedException(\Spatie\MediaLibrary\Exceptions\UnknownConversion::class);

        $media->getPath('unknownConversionName');
    }
}
