<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Test\TestCase;

class GetUrlTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_get_an_url_of_an_original_item()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaLibrary();

        $this->assertEquals($media->getUrl(), "/media/{$media->id}/test.jpg");
    }

    /**
     * @test
     */
    public function it_can_get_an_url_of_a_derived_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaLibrary();

        $conversionName = 'thumb';

        $this->assertEquals("/media/{$media->id}/conversions/{$conversionName}.jpg", $media->getUrl($conversionName));
    }

    /**
     * @test
     */
    public function it_returns_an_exception_when_getting_an_url_for_an_unknown_conversion()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaLibrary();

        $this->setExpectedException(\Spatie\MediaLibrary\Exceptions\UnknownConversion::class);

        $media->getUrl('unknownConversionName');
    }
}
