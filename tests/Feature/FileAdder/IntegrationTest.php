<?php

namespace Spatie\MediaLibrary\Tests\Feature\FileAdder;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\DiskDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidUrl;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
use Spatie\MediaLibrary\MediaCollections\Exceptions\RequestDoesNotHaveFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnknownType;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;
use Spatie\MediaLibrary\Tests\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class IntegrationTest extends TestCase
{
    /** @test */
    public function it_can_add_an_file_to_the_default_collection()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $this->assertEquals('default', $media->collection_name);
    }

    /** @test */
    public function toMediaCollection_has_an_alias_called_toMediaLibrary()
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
            ->toMediaCollection();
    }

    /** @test */
    public function it_can_set_the_name_of_the_media()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $this->assertEquals('test', $media->name);
    }

    /** @test */
    public function it_can_add_a_file_to_a_named_collection()
    {
        $collectionName = 'images';

        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection($collectionName);

        $this->assertEquals($collectionName, $media->collection_name);
    }

    /** @test */
    public function it_can_move_the_original_file_to_the_media_library()
    {
        $testFile = $this->getTestJpg();

        $media = $this->testModel
            ->addMedia($testFile)
            ->toMediaCollection();

        $this->assertFileDoesNotExist($testFile);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /** @test */
    public function it_can_copy_the_original_file_to_the_media_library()
    {
        $testFile = $this->getTestJpg();

        $media = $this->testModel
            ->copyMedia($testFile)
            ->toMediaCollection('images');

        $this->assertFileExists($testFile);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /** @test */
    public function it_can_handle_a_file_without_an_extension()
    {
        $media = $this->testModel
            ->addMedia($this->getTestFilesDirectory('test'))
            ->toMediaCollection();

        $this->assertEquals('test', $media->name);

        $this->assertEquals('test', $media->file_name);

        $this->assertEquals("/media/{$media->id}/test", $media->getUrl());

        $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test"));
    }

    /** @test */
    public function it_can_handle_an_image_file_without_an_extension()
    {
        $media = $this->testModel
            ->addMedia($this->getTestFilesDirectory('image'))
            ->toMediaCollection();

        $this->assertEquals('image', $media->type);
    }

    /** @test */
    public function it_can_handle_a_non_image_and_non_pdf_file()
    {
        $media = $this->testModel
            ->addMedia($this->getTestFilesDirectory('test.txt'))
            ->toMediaCollection();

        $this->assertEquals('test', $media->name);

        $this->assertEquals('test.txt', $media->file_name);

        $this->assertEquals("/media/{$media->id}/test.txt", $media->getUrl());

        $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test.txt"));
    }

    /** @test */
    public function it_can_add_an_upload_to_the_media_library()
    {
        $uploadedFile = new UploadedFile(
            $this->getTestFilesDirectory('test.jpg'),
            'alternativename.jpg',
            'image/jpeg',
            filesize($this->getTestFilesDirectory('test.jpg'))
        );

        $media = $this->testModel
            ->addMedia($uploadedFile)
            ->toMediaCollection();

        $this->assertEquals('alternativename', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /** @test */
    public function it_can_add_an_upload_to_the_media_library_from_the_current_request()
    {
        $this->app['router']->get('/upload', function () {
            $media = $this->testModel
                ->addMediaFromRequest('file')
                ->toMediaCollection();

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
    public function it_can_add_multiple_uploads_to_the_media_library_from_the_current_request()
    {
        $this->app['router']->get('/upload', function () {
            $fileAdders = collect(
                $this->testModel
                    ->addMultipleMediaFromRequest(['file-1', 'file-2'])
            );

            $fileAdders->each(function ($fileAdder) {
                $media = $fileAdder->toMediaCollection();

                $this->assertEquals('alternativename', $media->name);
                $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
            });

            $this->assertCount(2, $fileAdders);
        });

        $uploadedFiles = [
            'file-1' => new UploadedFile(
                $this->getTestJpg(),
                'alternativename.jpg',
                'image/jpeg',
                filesize($this->getTestJpg())
            ),
            'file-2' => new UploadedFile(
                $this->getTestSvg(),
                'alternativename.svg',
                'image/svg',
                filesize($this->getTestSvg())
            ),
        ];

        $result = $this->call('get', 'upload', [], [], $uploadedFiles);

        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test */
    public function it_can_add_handle_file_keys_that_contain_an_array_to_the_media_library_from_the_current_request()
    {
        $this->app['router']->get('/upload', function () {
            $fileAdders = collect(
                $this->testModel->addAllMediaFromRequest()
            );

            $fileAdders->each(function ($fileAdder) {
                $fileAdder = is_array($fileAdder) ? $fileAdder : [$fileAdder];

                foreach ($fileAdder as $item) {
                    $media = $item->toMediaCollection();

                    $this->assertEquals('alternativename', $media->name);
                    $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
                }
            });

            $this->assertCount(4, $fileAdders);
        });

        $uploadedFiles = [
            'file-1' => new UploadedFile(
                $this->getTestJpg(),
                'alternativename.jpg',
                'image/jpeg',
                filesize($this->getTestJpg())
            ),
            'file-2' => new UploadedFile(
                $this->getTestSvg(),
                'alternativename.svg',
                'image/svg',
                filesize($this->getTestSvg())
            ),
            'medias' => [
                new UploadedFile(
                    $this->getTestPng(),
                    'alternativename.png',
                    'image/png',
                    filesize($this->getTestPng())
                ),
                new UploadedFile(
                    $this->getTestWebm(),
                    'alternativename.webm',
                    'video/webm',
                    filesize($this->getTestWebm())
                ),
            ],
        ];

        $result = $this->call('get', 'upload', [], [], $uploadedFiles);

        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test */
    public function it_will_throw_an_exception_when_trying_to_add_a_non_existing_key_from_a_request()
    {
        $this->app['router']->get('/upload', function () {
            $exceptionWasThrown = false;

            try {
                $this->testModel
                    ->addMediaFromRequest('non existing key')
                    ->toMediaCollection();
            } catch (RequestDoesNotHaveFile $exception) {
                $exceptionWasThrown = true;
            }

            $this->assertTrue($exceptionWasThrown);
        });

        $this->call('get', 'upload');
    }

    /** @test */
    public function it_can_add_a_remote_file_to_the_media_library()
    {
        $url = 'https://docs.spatie.be/laravel-medialibrary/v8/images/header.jpg';

        $media = $this->testModel
            ->addMediaFromUrl($url)
            ->toMediaCollection();

        $this->assertEquals('header', $media->name);
        $this->assertFileExists($this->getMediaDirectory("{$media->id}/header.jpg"));
    }

    /** @test */
    public function it_will_not_add_local_files_when_an_url_is_expected()
    {
        $this->expectException(InvalidUrl::class);

        $this->testModel
            ->addMediaFromUrl(__FILE__)
            ->toMediaCollection();
    }

    /** @test */
    public function it_can_add_a_file_from_a_separate_disk_to_the_media_library()
    {
        Storage::disk('secondMediaDisk')->put('tmp/test.jpg', file_get_contents($this->getTestJpg()));

        $media = $this->testModel
            ->addMediaFromDisk('tmp/test.jpg', 'secondMediaDisk')
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory("{$media->id}/test.jpg"));
    }

    /** @test */
    public function it_can_natively_copy_a_remote_file_from_the_same_disk_to_the_media_library()
    {
        Storage::disk('public')->put('tmp/test.jpg', file_get_contents($this->getTestJpg()));
        $this->assertFileExists($this->getMediaDirectory('tmp/test.jpg'));

        $media = $this->testModel
            ->addMediaFromDisk('tmp/test.jpg', 'public')
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory("{$media->id}/test.jpg"));
        $this->assertFileDoesNotExist($this->getMediaDirectory('tmp/test.jpg'));
    }

    /** @test */
    public function it_can_add_a_remote_file_with_a_space_in_the_name_to_the_media_library()
    {
        $url = 'http://spatie.github.io/laravel-medialibrary/tests/TestSupport/testfiles/test%20with%20space.jpg';

        $media = $this->testModel
            ->addMediaFromUrl($url)
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory("{$media->id}/test-with-space.jpg"));
    }

    /** @test */
    public function it_can_add_a_remote_file_with_an_accent_in_the_name_to_the_media_library()
    {
        $url = 'https://orbit.brightbox.com/v1/acc-jqzwj/Marquis-Leisure/reviews/images/000/000/898/original/Antar%C3%A8sThumb.jpg';

        $media = $this->testModel
            ->addMediaFromUrl($url)
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory("{$media->id}/AntarèsThumb.jpg"));
    }

    /** @test */
    public function it_wil_thrown_an_exception_when_a_remote_file_could_not_be_added()
    {
        $url = 'https://docs.spatie.be/images/medialibrary/thisonedoesnotexist.jpg';

        $this->expectException(UnreachableUrl::class);

        $this->testModel
            ->addMediaFromUrl($url)
            ->toMediaCollection();
    }

    /** @test */
    public function it_wil_throw_an_exception_when_a_remote_file_has_an_invalid_mime_type()
    {
        $url = 'https://docs.spatie.be/laravel-medialibrary/v8/images/header.jpg';

        $this->expectException(MimeTypeNotAllowed::class);

        $this->testModel
            ->addMediaFromUrl($url, ['image/png'])
            ->toMediaCollection();
    }

    /** @test */
    public function it_can_rename_the_media_before_it_gets_added()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->usingName('othername')
            ->toMediaCollection();

        $this->assertEquals('othername', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));
    }

    /** @test */
    public function it_can_rename_the_file_before_it_gets_added()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->usingFileName('othertest.jpg')
            ->toMediaCollection();

        $this->assertEquals('test', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/othertest.jpg'));
    }

    /** @test */
    public function it_will_remove_strange_characters_from_the_file_name()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->usingFileName('other#test.jpg')
            ->toMediaCollection();

        $this->assertEquals('test', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/other-test.jpg'));
    }

    /** @test */
    public function it_will_sanitize_the_file_name_using_callable()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->sanitizingFileName(fn ($fileName) => 'new_file_name.jpg')
            ->toMediaCollection();

        $this->assertEquals('test', $media->name);
        $this->assertFileExists($this->getMediaDirectory($media->id.'/new_file_name.jpg'));
    }

    /** @test */
    public function it_can_save_media_in_the_right_order()
    {
        $media = [];
        foreach (range(0, 5) as $index) {
            $media[] = $this->testModel
                ->addMedia($this->getTestJpg())
                ->preservingOriginal()
                ->toMediaCollection();

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
            ->toMediaCollection();

        $this->assertEquals('testName', $media->name);

        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withAttributes(['name' => 'testName'])
            ->toMediaCollection();

        $this->assertEquals('testName', $media->name);
    }

    /** @test */
    public function it_can_add_manipulations_to_the_saved_media()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withManipulations(['thumb' => ['width' => '10']])
            ->toMediaCollection();

        $this->assertEquals('10', $media->manipulations['thumb']['width']);
    }

    /** @test */
    public function it_can_add_file_to_model_with_morph_map()
    {
        $media = $this->testModelWithMorphMap
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

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
        $this->app['config']->set('media-library.max_file_size', 1);

        $this->expectException(FileIsTooBig::class);

        $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_a_file_to_a_non_existing_disk()
    {
        $this->expectException(DiskDoesNotExist::class);

        $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('images', 'non-existing-disk');
    }

    /** @test */
    public function it_can_add_a_base64_encoded_file_to_the_media_library()
    {
        $testFile = $this->getTestJpg();
        $testBase64Data = base64_encode(file_get_contents($testFile));

        $media = $this->testModel
            ->addMediaFromBase64($testBase64Data)
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /** @test */
    public function a_string_can_be_accepted_to_be_added_to_the_media_library()
    {
        $string = 'test123';

        $media = $this->testModel
            ->addMediaFromString($string)
            ->toMediaCollection();

        $this->assertEquals($string, file_get_contents($media->getPath()));
    }

    /** @test */
    public function it_can_add_data_uri_prefixed_base64_encoded_file_to_the_medialibrary()
    {
        $testFile = $this->getTestJpg();
        $testBase64Data = 'data:image/jpg;base64,'.base64_encode(file_get_contents($testFile));

        $media = $this->testModel
            ->addMediaFromBase64($testBase64Data)
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_invalid_base64_data()
    {
        $testFile = $this->getTestJpg();
        $invalidBase64Data = file_get_contents($testFile);

        $this->expectException(InvalidBase64Data::class);

        $this->testModel
            ->addMediaFromBase64($invalidBase64Data)
            ->toMediaCollection();
    }

    /** @test */
    public function it_will_throw_an_exception_when_adding_invalid_base64_mime_type()
    {
        $testFile = $this->getTestJpg();
        $testBase64Data = base64_encode(file_get_contents($testFile));

        $this->expectException(MimeTypeNotAllowed::class);

        $this->testModel
            ->addMediaFromBase64($testBase64Data, ['image/png'])
            ->toMediaCollection();
    }

    /** @test */
    public function it_can_add_files_on_an_unsaved_model_even_if_not_preserving_the_original()
    {
        $this->testUnsavedModel->name = 'test';

        $this->testUnsavedModel->addMedia($this->getTestJpg())->toMediaCollection();

        $this->testUnsavedModel->addMedia($this->getTestPdf())->toMediaCollection();

        $this->testUnsavedModel->save();

        $media = $this->testUnsavedModel->getMedia();

        $this->assertCount(2, $media);
    }

    /** @test */
    public function it_can_add_an_upload_to_the_media_library_using_dot_notation()
    {
        $this->app['router']->get('/upload', function () {
            $media = $this->testModel
                ->addMediaFromRequest('file.name')
                ->toMediaCollection();

            $this->assertEquals('alternativename', $media->name);
            $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
        });

        $fileUpload = new UploadedFile(
            $this->getTestFilesDirectory('test.jpg'),
            'alternativename.jpg',
            'image/jpeg',
            filesize($this->getTestFilesDirectory('test.jpg'))
        );

        $result = $this->call('get', 'upload', [], [], ['file' => ['name'=>$fileUpload]]);

        $this->assertEquals(200, $result->getStatusCode());
    }
}
