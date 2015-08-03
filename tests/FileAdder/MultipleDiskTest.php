<?php

namespace Spatie\MediaLibrary\Test\HasMediaTrait;

use Spatie\MediaLibrary\Exceptions\FilesystemDoesNotExist;
use Spatie\MediaLibrary\Test\TestCase;

class MultipleDiskTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_add_a_file_to_a_named_collection_on_a_specific_disk()
    {
        $collectionName = 'images';
        $diskName = 'secondMediaDisk';

        $media = $this->testModel->addMedia($this->getTestJpg())->toCollectionOnDisk($collectionName, $diskName);

        $this->assertEquals($collectionName, $media->collection_name);
        $this->assertEquals($diskName, $media->disk);
        $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_when_using_a_non_existing_disk()
    {
        $this->setExpectedException(FileSystemDoesNotExist::class);
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->toCollectionOnDisk('images', 'diskdoesnotexist');
    }

    /**
     * @test
     */
    public function it_can_save_derived_images_on_a_specific_disk()
    {
        $collectionName = 'images';
        $diskName = 'secondMediaDisk';

        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toCollectionOnDisk($collectionName, $diskName);

        $this->assertEquals($collectionName, $media->collection_name);
        $this->assertEquals($diskName, $media->disk);
        $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
        $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/conversions/thumb.jpg');
    }

    /**
     * @test
     */
    public function it_can_handle_generate_urls_to_media_on_an_alternative_disk()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaLibraryOnDisk('', 'secondMediaDisk');

        $this->assertEquals("/media2/{$media->id}/test.jpg", $media->getUrl());
        $this->assertEquals("/media2/{$media->id}/conversions/thumb.jpg", $media->getUrl('thumb'));
    }
}
