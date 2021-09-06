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

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $expected = str_replace('/', '\\', $this->getMediaDirectory() . "/{$media->id}/test.jpg");
            $actual = str_replace('/', '\\', $media->getPath());

            $this->assertEquals($expected, $actual);

            return 'windows-os-test';
        }

        $this->assertEquals($this->getMediaDirectory() . "/{$media->id}/test.jpg", $media->getPath());

        return 'linux-os-test';
    }

    /** @test */
    public function it_can_get_a_path_of_a_derived_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $conversionName = 'thumb';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $expected = str_replace('/', '\\', $this->getMediaDirectory() . "/{$media->id}/conversions/test-{$conversionName}.jpg");
            $actual = str_replace('/', '\\', $media->getPath($conversionName));

            $this->assertEquals($expected, $actual);

            return 'windows-os-test';
        }

        $this->assertEquals(
            $this->getMediaDirectory() . "/{$media->id}/conversions/test-{$conversionName}.jpg",
            $media->getPath($conversionName)
        );

        return 'linux-os-test';
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

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $expected = str_replace('/', '\\', $this->getMediaDirectory() . "/prefix/{$media->id}/test.jpg");
            $actual = str_replace('/', '\\', $media->getPath());

            $this->assertEquals($expected, $actual);

            return 'windows-os-test';
        }

        $this->assertEquals($this->getMediaDirectory() . "/prefix/{$media->id}/test.jpg", $media->getPath());

        return 'linux-os-test';
    }

    /** @test */
    public function it_can_get_a_path_of_a_derived_image_with_prefix()
    {
        config(['media-library.prefix' => 'prefix']);

        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $conversionName = 'thumb';

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $expected = str_replace('/', '\\', $this->getMediaDirectory() . "/prefix/{$media->id}/conversions/test-{$conversionName}.jpg");
            $actual = str_replace('/', '\\', $media->getPath($conversionName));

            $this->assertEquals($expected, $actual);

            return 'windows-os-test';
        }

        $this->assertEquals(
            $this->getMediaDirectory() . "/prefix/{$media->id}/conversions/test-{$conversionName}.jpg",
            $media->getPath($conversionName)
        );

        return 'linux-os-test';
    }

}
