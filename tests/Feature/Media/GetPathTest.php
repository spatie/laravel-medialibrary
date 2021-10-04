<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\Tests\TestCase;

class GetPathTest extends TestCase
{
    /** @test */
    public function it_can_get_a_path_of_an_original_item()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        $expected = $this->makePathOsSafe($this->getMediaDirectory() . "/{$media->id}/test.jpg");

        $actual = $this->makePathOsSafe($media->getPath());

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function it_can_get_a_path_of_a_derived_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $conversionName = 'thumb';

        $expected = $this->makePathOsSafe($this->getMediaDirectory() . "/{$media->id}/conversions/test-{$conversionName}.jpg");

        $actual = $this->makePathOsSafe($media->getPath($conversionName));

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function it_returns_an_exception_when_getting_a_path_for_an_unknown_conversion()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        $this->expectException(InvalidConversion::class);

        $media->getPath('unknownConversionName');
    }

    /** @test */
    public function it_can_get_a_path_of_an_original_item_with_prefix()
    {
        config(['media-library.prefix' => 'prefix']);

        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        $expected = $this->makePathOsSafe($this->getMediaDirectory() . "/prefix/{$media->id}/test.jpg");

        $actual = $this->makePathOsSafe($media->getPath());

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function it_can_get_a_path_of_a_derived_image_with_prefix()
    {
        config(['media-library.prefix' => 'prefix']);

        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $conversionName = 'thumb';

        $expected = $this->makePathOsSafe($this->getMediaDirectory() . "/prefix/{$media->id}/conversions/test-{$conversionName}.jpg");

        $actual = $this->makePathOsSafe($media->getPath($conversionName));

        $this->assertEquals($expected, $actual);
    }
}
