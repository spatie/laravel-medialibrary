<?php

namespace Spatie\MediaLibrary\Test\FileAdder;

use Spatie\MediaLibrary\Test\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class IntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_add_an_file_to_the_default_collection()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaLibrary();

        $this->assertEquals('default', $media->collection_name);
    }

    /**
     * @test
     */
    public function it_can_set_the_name_of_the_media()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaLibrary();

        $this->assertEquals('test', $media->name);
    }

    /**
     * @test
     */
    public function it_can_add_a_file_to_a_named_collection()
    {
        $collectionName = 'images';

        $media = $this->testModel->addMedia($this->getTestJpg())->toCollection($collectionName);

        $this->assertEquals($collectionName, $media->collection_name);
    }

    /**
     * @test
     */
    public function it_can_move_the_original_file_to_the_medialibrary()
    {
        $testFile = $this->getTestJpg();

        $media = $this->testModel
            ->addMedia($testFile)
            ->toMediaLibrary();

        $this->assertFileNotExists($testFile);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /**
     * @test
     */
    public function it_can_copy_the_original_file_to_the_medialibrary()
    {
        $testFile = $this->getTestJpg();

        $media = $this->testModel->copyMedia($testFile)->toCollection('images');

        $this->assertFileExists($testFile);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /**
     * @test
     */
    public function it_can_handle_a_file_without_an_extension()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test'))->toMediaLibrary();

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
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.txt'))->toMediaLibrary();

        $this->assertEquals('test', $media->name);

        $this->assertEquals('test.txt', $media->file_name);

        $this->assertEquals("/media/{$media->id}/test.txt", $media->getUrl());

        $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test.txt"));
    }

    /**
     * @test
     */
    public function it_will_put_a_gitignore_in_the_medialibrary_when_adding_media()
    {
        $this->assertFileNotExists($this->getMediaDirectory('.gitignore'));

        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaLibrary();

        $this->assertFileExists($this->getMediaDirectory('.gitignore'));
    }

    /**
     * @test
     */
    public function it_can_add_an_upload_to_the_medialibrary()
    {
        $uploadedFile = new UploadedFile(
            $this->getTestFilesDirectory('test.jpg'),
            'alternativename.jpg',
            'image/jpeg',
            filesize($this->getTestFilesDirectory('test.jpg'))
        );

        $media = $this->testModel->addMedia($uploadedFile)->toMediaLibrary();
        $this->assertEquals('alternativename', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /**
     * @test
     */
    public function it_can_rename_the_media_before_it_gets_added()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->usingName('othername')
            ->toMediaLibrary();

        $this->assertEquals('othername', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));
    }

    /**
     * @test
     */
    public function it_can_rename_the_file_before_it_gets_added()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->usingFileName('othertest.jpg')
            ->toMediaLibrary();

        $this->assertEquals('test', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/othertest.jpg'));
    }

    /**
     * @test
     */
    public function it_can_save_media_in_the_right_order()
    {
        $media = [];
        foreach (range(0, 5) as $index) {
            $media[] = $this->testModel
                ->addMedia($this->getTestJpg())
                ->preservingOriginal()
                ->toMediaLibrary();

            $this->assertEquals($index + 1, $media[$index]->order_column);
        }
    }
}
