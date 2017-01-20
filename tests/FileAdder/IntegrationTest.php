<?php

namespace Spatie\MediaLibrary\Test\FileAdder;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\ModelDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\RequestDoesNotHaveFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnknownType;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnreachableUrl;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Test\TestModel;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class IntegrationTest extends TestCase
{
    /** @test */
    public function it_can_add_an_file_to_the_default_collection()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaLibrary();

        $this->assertEquals('default', $media->collection_name);
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_a_non_existing_file()
    {
        $this->expectException(FileDoesNotExist::class);

        $this->testModel
            ->addMedia('this-file-does-not-exist.jpg')
            ->toMediaLibrary();
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_a_non_saved_model()
    {
        $this->expectException(ModelDoesNotExist::class);

        (new TestModel())
            ->addMedia($this->getTestJpg())
            ->toMediaLibrary();
    }

    /** @test */
    public function it_can_set_the_name_of_the_media()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaLibrary();

        $this->assertEquals('test', $media->name);
    }

    /** @test */
    public function it_can_add_a_file_to_a_named_collection()
    {
        $collectionName = 'images';

        $media = $this->testModel->addMedia($this->getTestJpg())->toCollection($collectionName);

        $this->assertEquals($collectionName, $media->collection_name);
    }

    /** @test */
    public function it_can_move_the_original_file_to_the_medialibrary()
    {
        $testFile = $this->getTestJpg();

        $media = $this->testModel
            ->addMedia($testFile)
            ->toMediaLibrary();

        $this->assertFileNotExists($testFile);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /** @test */
    public function it_can_copy_the_original_file_to_the_medialibrary()
    {
        $testFile = $this->getTestJpg();

        $media = $this->testModel->copyMedia($testFile)->toCollection('images');

        $this->assertFileExists($testFile);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /** @test */
    public function it_can_handle_a_file_without_an_extension()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test'))->toMediaLibrary();

        $this->assertEquals('test', $media->name);

        $this->assertEquals('test', $media->file_name);

        $this->assertEquals("/media/{$media->id}/test", $media->getUrl());

        $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test"));
    }

    /** @test */
    public function it_can_handle_an_image_file_without_an_extension()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('image'))->toMediaLibrary();

        $this->assertEquals(Media::TYPE_IMAGE, $media->type);
    }

    /** @test */
    public function it_can_handle_a_non_image_and_non_pdf_file()
    {
        $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.txt'))->toMediaLibrary();

        $this->assertEquals('test', $media->name);

        $this->assertEquals('test.txt', $media->file_name);

        $this->assertEquals("/media/{$media->id}/test.txt", $media->getUrl());

        $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test.txt"));
    }

    /** @test */
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

    /** @test */
    public function it_can_add_an_upload_to_the_medialibrary_from_the_current_request()
    {
        $this->app['router']->get('/upload', function () {
            $media = $this->testModel->addMediaFromRequest('file')->toMediaLibrary();
            $this->assertEquals('alternativename', $media->name);
            $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
        });

        $fileUpload = new UploadedFile(
            $this->getTestFilesDirectory('test.jpg'),
            'alternativename.jpg',
            'image/jpeg',
            filesize($this->getTestFilesDirectory('test.jpg'))
        );

        $result = $this->call('get', 'upload', [], [], ['file' => $fileUpload]);

        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test */
    public function it_will_throw_an_exception_when_trying_to_add_a_non_existing_key_from_a_request()
    {
        $this->app['router']->get('/upload', function () {
            $exceptionWasThrown = false;

            try {
                $this->testModel->addMediaFromRequest('non existing key')->toMediaLibrary();
            } catch (RequestDoesNotHaveFile $exception) {
                $exceptionWasThrown = true;
            }

            $this->assertTrue($exceptionWasThrown);
        });

        $this->call('get', 'upload');
    }

    /** @test */
    public function it_can_add_a_remote_file_to_the_medialibrary()
    {
        $url = 'https://docs.spatie.be/images/medialibrary/header.jpg';

        $media = $this->testModel
            ->addMediaFromUrl($url)
            ->toMediaLibrary();

        $this->assertEquals('header', $media->name);
        $this->assertFileExists($this->getMediaDirectory("{$media->id}/header.jpg"));
    }

    /** @test */
    public function it_wil_thrown_an_exception_when_a_remote_file_could_not_be_added()
    {
        $url = 'https://docs.spatie.be/images/medialibrary/thisonedoesnotexist.jpg';

        $this->expectException(UnreachableUrl::class);

        $this->testModel
            ->addMediaFromUrl($url)
            ->toMediaLibrary();
    }

    /** @test */
    public function it_can_rename_the_media_before_it_gets_added()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->usingName('othername')
            ->toMediaLibrary();

        $this->assertEquals('othername', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));
    }

    /** @test */
    public function it_can_rename_the_file_before_it_gets_added()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->usingFileName('othertest.jpg')
            ->toMediaLibrary();

        $this->assertEquals('test', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/othertest.jpg'));
    }

    /** @test */
    public function it_will_sanitize_the_file_name()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->usingFileName('other#test.jpg')
            ->toMediaLibrary();

        $this->assertEquals('test', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/other-test.jpg'));
    }

    /** @test */
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

    /** @test */
    public function it_can_add_properties_to_the_saved_media()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withProperties(['name' => 'testName'])
            ->toMediaLibrary();

        $this->assertEquals('testName', $media->name);

        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withAttributes(['name' => 'testName'])
            ->toMediaLibrary();

        $this->assertEquals('testName', $media->name);
    }

    /** @test */
    public function it_can_add_file_to_model_with_morph_map()
    {
        $media = $this->testModelWithMorphMap
            ->addMedia($this->getTestJpg())
            ->toMediaLibrary();

        $this->assertEquals($this->testModelWithMorphMap->getMorphClass(), $media->model_type);
    }

    /** @test */
    public function it_will_throw_an_exception_when_setting_the_file_to_a_wrong_type()
    {
        $wrongType = [];

        $this->expectException(UnknownType::class);

        $this->testModel
            ->addMedia($this->getTestJpg())
            ->setFile($wrongType);
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_a_file_that_is_too_big()
    {
        $this->app['config']->set('laravel-medialibrary.max_file_size', 1);

        $this->expectException(FileIsTooBig::class);

        $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaLibrary();
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_a_file_to_a_non_existing_disk()
    {
        $this->expectException(DiskDoesNotExist::class);

        $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaLibraryOnDisk('images', 'non-existing-disk');
    }
}
