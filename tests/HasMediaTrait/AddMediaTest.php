<?php

namespace Spatie\MediaLibrary\Test\HasMediaTrait;

use Spatie\MediaLibrary\Test\TestCase;

class AddMediaTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_add_an_file_to_the_default_collection()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'));

        $this->assertEquals('default', $media->collection_name);
    }

    /**
     * @test
     */
    public function it_can_set_the_name_of_the_media()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'));

        $this->assertEquals('test', $media->name);
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

    /**
     * @test
     */
    public function it_can_create_a_derived_version_of_an_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'), 'images', true, true);

        $this->assertFileExists($this->getMediaDirectory($media->id.'/conversions/thumb.jpg'));
    }

    /**
     * @test
     */
    public function it_can_create_a_derived_version_of_a_pdf_if_imagick_exists()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.pdf'), 'images', true, true);

        $thumbPath = $this->getMediaDirectory($media->id.'/conversions/thumb.jpg');

        class_exists('Imagick') ? $this->assertFileExists($thumbPath) : $this->assertFileNotExists($thumbPath);
    }

    /**
     * @test
     */
    public function it_can_handle_a_file_without_an_extension()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test'));

        $this->assertEquals('test', $media->name);

        $this->assertEquals('test', $media->file_name);

        $this->assertEquals("/media/{$media->id}/test", $media->getUrl());

        $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test"));
    }

    /**
     * @test
     */
    public function it_can_handle_a_non_image_and_non_pdf_file()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.txt'));

        $this->assertEquals('test', $media->name);

        $this->assertEquals('test.txt', $media->file_name);

        $this->assertEquals("/media/{$media->id}/test.txt", $media->getUrl());

        $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test.txt"));
    }


    /**
     * @test
     */
    public function it_will_not_create_a_derived_version_for_non_registered_collections()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'), 'downloads', true, true);

        $this->assertFileNotExists($this->getMediaDirectory($media->id.'/conversions/thumb.jpg'));
    }

    /**
     * @test
     */
    public function it_will_put_a_gitignore_in_the_medialibrary_when_adding_media()
    {
        $this->assertFileNotExists($this->getMediaDirectory('.gitignore'));

        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'));

        $this->assertFileExists($this->getMediaDirectory('.gitignore'));
    }
}
