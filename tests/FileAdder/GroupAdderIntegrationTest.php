<?php

namespace Spatie\MediaLibrary\Test\FileAdder;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\RequestDoesNotHaveFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\ModelDoesNotExist;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Test\TestModel;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnknownType;

class GroupAdderIntegrationTest extends TestCase
{
    protected $testFiles;

    public function setUp()
    {
        parent::setUp();

        $this->testFiles = [$this->getTestJpg(), $this->getTestSvg()];
    }

    /** @test */
    public function it_can_add_multiple_files_to_the_default_collection()
    {
        $mediaCollection = $this->testModel
            ->addMedia($this->testFiles)
            ->toMediaLibrary();

        $mediaCollection->each(function (Media $media) {
            $this->assertEquals('default', $media->collection_name);
        });
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_a_non_existing_file()
    {
        $this->expectException(FileDoesNotExist::class);

        $this->testModel
            ->addMedia(['this-file-does-not-exist.jpg', $this->getTestSvg()])
            ->toMediaLibrary();
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_to_a_non_saved_model()
    {
        $this->expectException(ModelDoesNotExist::class);

        (new TestModel())
            ->addMedia($this->testFiles)
            ->toMediaLibrary();
    }

    /** @test */
    public function it_can_set_the_names_of_the_media()
    {
        $mediaCollection = $this->testModel
            ->addMedia($this->testFiles)
            ->toMediaLibrary();

        $mediaCollection->each(function (Media $media) {
            $this->assertEquals('test', $media->name);
        });
    }

    /** @test */
    public function it_can_add_files_to_a_named_collection()
    {
        $collectionName = 'images';

        $mediaCollection = $this->testModel
            ->addMedia($this->testFiles)
            ->toMediaLibrary($collectionName);

        $mediaCollection->each(function (Media $media) use ($collectionName) {
            $this->assertEquals($collectionName, $media->collection_name);
        });
    }

    /** @test */
    public function it_can_move_the_original_files_to_the_medialibrary()
    {
        $mediaCollection = $this->testModel
            ->addMedia($this->testFiles)
            ->toMediaLibrary();

        foreach($this->testFiles as $testFile) {
            $this->assertFileNotExists($testFile);
        }

        $mediaCollection->each(function (Media $media) {
            $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
        });
    }

    /** @test */
    public function it_can_copy_the_original_files_to_the_medialibrary()
    {
        $mediaCollection = $this->testModel
            ->copyMedia($this->testFiles)
            ->toMediaLibrary('images');

        foreach($this->testFiles as $testFile) {
            $this->assertFileExists($testFile);
        }

        $mediaCollection->each(function (Media $media) {
            $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
        });
    }

    /** @test */
    public function it_can_handle_files_without_an_extension()
    {
        $mediaCollection = $this->testModel
            ->addMedia([$this->getTestFilesDirectory('test')])
            ->toMediaLibrary();

        $media = $mediaCollection->first();

        $this->assertEquals('test', $media->name);

        $this->assertEquals('test', $media->file_name);

        $this->assertEquals("/media/{$media->id}/test", $media->getUrl());

        $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test"));
    }

    /** @test */
    public function it_can_handle_image_files_without_an_extension()
    {
        $mediaCollection = $this->testModel
            ->addMedia([$this->getTestFilesDirectory('image')])
            ->toMediaLibrary();

        $media = $mediaCollection->first();

        $this->assertEquals('image', $media->type);
    }

    /** @test */
    public function it_can_handle_a_non_image_and_non_pdf_file()
    {
        $mediaCollection = $this->testModel
            ->addMedia([$this->getTestFilesDirectory('test.txt')])
            ->toMediaLibrary();

        $media = $mediaCollection->first();

        $this->assertEquals('test', $media->name);

        $this->assertEquals('test.txt', $media->file_name);

        $this->assertEquals("/media/{$media->id}/test.txt", $media->getUrl());

        $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test.txt"));
    }

    /** @test */
    public function it_can_add_uploaded_files_to_the_medialibrary()
    {
        $uploadedFiles = [
            new UploadedFile(
                $this->getTestJpg(),
                'alternativename.jpg',
                'image/jpeg',
                filesize($this->getTestJpg())
            ),
            new UploadedFile(
                $this->getTestSvg(),
                'alternativename.svg',
                'image/svg',
                filesize($this->getTestSvg())
            )
        ];

        $mediaCollection = $this->testModel
            ->addMedia($uploadedFiles)
            ->toMediaLibrary();

        $mediaCollection->each(function (Media $media) {
            $this->assertEquals($media->name, 'alternativename');
            $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
        });
    }

    /** @test */
    public function it_can_add_uploaded_files_to_the_medialibrary_from_the_current_request()
    {
        $this->app['router']->get('/upload', function () {
            $mediaCollection = $this->testModel
                ->addMediaFromRequest(['file-one', 'file-two'])
                ->toMediaLibrary();

            $mediaCollection->each(function (Media $media) {
                $this->assertEquals($media->name, 'alternativename');
                $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
            });
        });

        $fileUploads = [
            'file-one' => new UploadedFile(
                $this->getTestJpg(),
                'alternativename.jpg',
                'image/jpeg',
                filesize($this->getTestJpg())
            ),
            'file-two' => new UploadedFile(
                $this->getTestSvg(),
                'alternativename.svg',
                'image/svg',
                filesize($this->getTestSvg())
            )
        ];

        $result = $this->call('get', 'upload', [], [], $fileUploads);

        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test */
    public function it_will_throw_an_exception_when_trying_to_add_a_non_existing_key_from_a_request()
    {
        $this->app['router']->get('/upload', function () {
            $exceptionWasThrown = false;

            try {
                $this->testModel
                    ->addMediaFromRequest(['non existing key'])
                    ->toMediaLibrary();
            } catch (RequestDoesNotHaveFile $exception) {
                $exceptionWasThrown = true;
            }

            $this->assertTrue($exceptionWasThrown);
        });

        $this->call('get', 'upload');
    }

    /** @test */
    public function it_can_rename_the_media_before_it_gets_added()
    {
        $mediaCollection = $this->testModel
            ->addMedia([$this->getTestJpg(), $this->getTestSvg()])
            ->usingName('othername')
            ->toMediaLibrary();

        $mediaCollection->each(function (Media $media) {
            $this->assertEquals($media->name, 'othername');
        });
    }

    /** @test */
    public function it_can_rename_the_files_before_they_gets_added()
    {
        $media = $this->testModel
            ->addMedia([$this->getTestJpg()])
            ->usingFileName('othertest.jpg')
            ->toMediaLibrary();

        $this->assertEquals('test', $media->first()->name);
        $this->assertFileExists($this->getMediaDirectory($media->first()->id.'/othertest.jpg'));
    }

    /** @test */
    public function it_will_sanitize_the_file_name()
    {
        $media = $this->testModel
            ->addMedia([$this->getTestJpg()])
            ->usingFileName('other#test.jpg')
            ->toMediaLibrary();

        $this->assertEquals('test', $media->first()->name);
        $this->assertFileExists($this->getMediaDirectory($media->first()->id.'/other-test.jpg'));
    }

    /** @test */
    public function it_can_add_properties_to_the_saved_media()
    {
        $media = $this->testModel
            ->addMedia([$this->getTestJpg()])
            ->preservingOriginal()
            ->withProperties(['name' => 'testName'])
            ->toMediaLibrary();

        $this->assertEquals('testName', $media->first()->name);

        $media = $this->testModel
            ->addMedia([$this->getTestJpg()])
            ->preservingOriginal()
            ->withAttributes(['name' => 'testName'])
            ->toMediaLibrary();

        $this->assertEquals('testName', $media->first()->name);
    }

    /** @test */
    public function it_will_throw_an_exception_when_setting_the_files_to_a_wrong_type()
    {
        $wrongType = [];

        $this->expectException(UnknownType::class);

        $this->testModel
            ->addMedia($this->testFiles)
            ->setFile($wrongType);
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_files_that_are_too_big()
    {
        $this->app['config']->set('medialibrary.max_file_size', 1);

        $this->expectException(FileIsTooBig::class);

        $this->testModel
            ->addMedia($this->testFiles)
            ->toMediaLibrary();
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_files_to_a_non_existing_disk()
    {
        $this->expectException(DiskDoesNotExist::class);

        $this->testModel
            ->addMedia($this->testFiles)
            ->toMediaLibrary('images', 'non-existing-disk');
    }
}