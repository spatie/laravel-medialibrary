<?php

namespace Spatie\MediaLibrary\Test\HasMediaTrait;

class AddMediaTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_add_a_file_to_the_default_collection()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'));

        $this->assertEquals('default', $media->collection_name);
    }

    /**
     * @test
     */
    public function it_can_add_a_file_to_a_named_collection()
    {
        $collectionName = 'images';

        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'), $collectionName);

        $this->assertEquals($collectionName, $media->collection_name);
    }

    /**
     * @test
     */
    public function it_can_move_the_original_file_to_the_medialibrary()
    {
        $testFile = $this->getTestFilesDirectory('test.jpg');

        $media = $this->testModel->addMedia($testFile);

        $this->assertFileNotExists($testFile);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /**
     * @test
     */
    public function it_can_copy_the_original_file_to_the_medialibrary()
    {
        $testFile = $this->getTestFilesDirectory('test.jpg');

        $media = $this->testModel->addMedia($testFile, 'images', false);

        $this->assertFileExists($testFile);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /**
     * @test
     */
    public function it_wil_not_mark_the_media_as_temporary_by_default()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'), 'images', true);

        $this->assertEquals(false, $media->temp);
    }

    /**
     * @test
     */
    public function it_can_mark_the_media_as_temporary()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'), 'images', true, true);

        $this->assertEquals(true, $media->temp);
    }


}
