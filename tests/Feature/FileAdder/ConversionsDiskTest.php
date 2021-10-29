<?php

namespace Spatie\MediaLibrary\Tests\Feature\FileAdder;

use Spatie\MediaLibrary\Tests\TestCase;

class ConversionsDiskTest extends TestCase
{
    /** @test */
    public function it_can_save_conversions_on_a_separate_disk()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->storingConversionsOnDisk('secondMediaDisk')
            ->toMediaCollection();

        $this->assertEquals('public', $media->disk);
        $this->assertEquals('secondMediaDisk', $media->conversions_disk);

        $this->assertEquals("/media/{$media->id}/test.jpg", $media->getUrl());
        $this->assertEquals("/media2/{$media->id}/conversions/test-thumb.jpg", $media->getUrl('thumb'));

        $originalFilePath = $media->getPath();

        $this->assertEquals(
            $this->getTestsPath('TestSupport/temp/media/1/test.jpg'),
            $originalFilePath
        );
        $this->assertFileExists($originalFilePath);

        $conversionsFilePath = $media->getPath('thumb');
        $this->assertEquals(
            $this->getTestsPath('TestSupport/temp/media2/1/conversions/test-thumb.jpg'),
            $conversionsFilePath
        );
        $this->assertFileExists($conversionsFilePath);
    }

    /** @test */
    public function the_responsive_images_will_get_saved_on_the_same_disk_as_the_conversions()
    {
        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->storingConversionsOnDisk('secondMediaDisk')
            ->toMediaCollection();

        $this->assertFileExists($this->getTempDirectory('media2/1/responsive-images/test___thumb_50_41.jpg'));
    }

    /** @test */
    public function deleting_media_will_also_delete_conversions_on_the_separate_disk()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->storingConversionsOnDisk('secondMediaDisk')
            ->toMediaCollection();

        $this->assertFileExists($media->getPath('thumb'));

        $media->delete();

        $this->assertFileDoesNotExist($media->getPath('thumb'));

        $originalFilePath = $media->getPath();
        $this->assertFileDoesNotExist($originalFilePath);
    }

    /** @test */
    public function it_will_store_the_conversion_on_the_disk_specified_in_on_the_media_collection()
    {
        $media = $this->testModelWithConversionsOnOtherDisk
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('thumb');

        $conversionsFilePath = $media->getPath('thumb');
        $this->assertEquals(
            $this->getTestsPath('TestSupport/temp/media2/1/conversions/test-thumb.jpg'),
            $conversionsFilePath
        );
        $this->assertFileExists($conversionsFilePath);
    }
}
