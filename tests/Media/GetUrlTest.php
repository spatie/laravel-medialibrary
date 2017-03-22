<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Exceptions\InvalidConversion;

class GetUrlTest extends TestCase
{
    /** @test */
    public function it_can_get_an_url_of_an_original_item()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertEquals($media->getUrl(), "/media/{$media->id}/test.jpg");
    }

    /** @test */
    public function it_can_get_an_url_of_a_derived_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $conversionName = 'thumb';

        $this->assertEquals("/media/{$media->id}/conversions/{$conversionName}.jpg", $media->getUrl($conversionName));
    }

    /** @test */
    public function it_returns_an_exception_when_getting_an_url_for_an_unknown_conversion()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        $this->expectException(InvalidConversion::class);

        $media->getUrl('unknownConversionName');
    }

    /** @test */
    public function it_wil_url_encode_the_file_name_when_generating_an_url()
    {
        $this->testModel->addMedia($this->getTestFilesDirectory('test with space.jpg'))->toMediaCollection();

        $this->assertEquals('/media/1/test%20with%20space.jpg', $this->testModel->getFirstMediaUrl());
    }

    /** @test */
    public function it_can_get_the_full_url_of_an_original_item()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertEquals($media->getFullUrl(), "http://localhost/media/{$media->id}/test.jpg");
    }

    /** @test */
    public function it_can_get_the_full_url_of_a_derived_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $conversionName = 'thumb';

        $this->assertEquals("http://localhost/media/{$media->id}/conversions/{$conversionName}.jpg", $media->getFullUrl($conversionName));
    }
}
