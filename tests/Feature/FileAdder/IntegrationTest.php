<?php

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
use Spatie\MediaLibrary\Tests\TestSupport\RenameOriginalFileNamer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

uses(TestCase::class);

it('can add an file to the default collection', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $this->assertEquals('default', $media->collection_name);
});

test('to media collection has an alias called to media library', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaLibrary();

    $this->assertEquals('default', $media->collection_name);
});

it('will throw an exception when adding a non existing file', function () {
    $this->expectException(FileDoesNotExist::class);

    $this->testModel
        ->addMedia('this-file-does-not-exist.jpg')
        ->toMediaCollection();
});

it('can set the name of the media', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $this->assertEquals('test', $media->name);
});

it('can add a file to a named collection', function () {
    $collectionName = 'images';

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection($collectionName);

    $this->assertEquals($collectionName, $media->collection_name);
});

it('can move the original file to the media library', function () {
    $testFile = $this->getTestJpg();

    $media = $this->testModel
        ->addMedia($testFile)
        ->toMediaCollection();

    $this->assertFileDoesNotExist($testFile);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
});

it('can copy the original file to the media library', function () {
    $testFile = $this->getTestJpg();

    $media = $this->testModel
        ->copyMedia($testFile)
        ->toMediaCollection('images');

    $this->assertFileExists($testFile);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
});

it('can handle a file without an extension', function () {
    $media = $this->testModel
        ->addMedia($this->getTestFilesDirectory('test'))
        ->toMediaCollection();

    $this->assertEquals('test', $media->name);

    $this->assertEquals('test', $media->file_name);

    $this->assertEquals("/media/{$media->id}/test", $media->getUrl());

    $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test"));
});

it('can handle an image file without an extension', function () {
    $media = $this->testModel
        ->addMedia($this->getTestFilesDirectory('image'))
        ->toMediaCollection();

    $this->assertEquals('image', $media->type);
});

it('can handle a non image and non pdf file', function () {
    $media = $this->testModel
        ->addMedia($this->getTestFilesDirectory('test.txt'))
        ->toMediaCollection();

    $this->assertEquals('test', $media->name);

    $this->assertEquals('test.txt', $media->file_name);

    $this->assertEquals("/media/{$media->id}/test.txt", $media->getUrl());

    $this->assertFileExists($this->getMediaDirectory("/{$media->id}/test.txt"));
});

it('can add an upload to the media library', function () {
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
});

it('can add an upload to the media library from the current request', function () {
    app()['router']->get('/upload', function () {
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

    $this->withoutExceptionHandling();

    $result = $this->call('get', 'upload', [], [], ['file' => $fileUpload]);

    $this->assertEquals(200, $result->getStatusCode());
});

it('can add multiple uploads to the media library from the current request', function () {
    app()['router']->get('/upload', function () {
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
});

it('can add handle file keys that contain an array to the media library from the current request', function () {
    app()['router']->get('/upload', function () {
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
});

it('will throw an exception when trying to add a non existing key from a request', function () {
    app()['router']->get('/upload', function () {
        $exceptionWasThrown = false;

        try {
            $this->testModel
                ->addMediaFromRequest('non existing key')
                ->toMediaCollection();
        } catch (RequestDoesNotHaveFile) {
            $exceptionWasThrown = true;
        }

        $this->assertTrue($exceptionWasThrown);
    });

    $this->call('get', 'upload');
});

it('can add a remote file to the media library', function () {
    $url = 'https://spatie.be/docs/laravel-medialibrary/v9/images/header.jpg';

    $media = $this->testModel
        ->addMediaFromUrl($url)
        ->toMediaCollection();

    $this->assertEquals('header', $media->name);
    $this->assertFileExists($this->getMediaDirectory("{$media->id}/header.jpg"));
});

it('will not add local files when an url is expected', function () {
    $this->expectException(InvalidUrl::class);

    $this->testModel
        ->addMediaFromUrl(__FILE__)
        ->toMediaCollection();
});

it('can add a file from a separate disk to the media library', function () {
    Storage::disk('secondMediaDisk')->put('tmp/test.jpg', file_get_contents($this->getTestJpg()));

    $media = $this->testModel
        ->addMediaFromDisk('tmp/test.jpg', 'secondMediaDisk')
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory("{$media->id}/test.jpg"));
});

it('can natively copy a remote file from the same disk to the media library', function () {
    Storage::disk('public')->put('tmp/test.jpg', file_get_contents($this->getTestJpg()));
    $this->assertFileExists($this->getMediaDirectory('tmp/test.jpg'));

    $media = $this->testModel
        ->addMediaFromDisk('tmp/test.jpg', 'public')
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory("{$media->id}/test.jpg"));
    $this->assertFileDoesNotExist($this->getMediaDirectory('tmp/test.jpg'));
});

it('can add a remote file with a space in the name to the media library', function () {
    $url = 'http://spatie.github.io/laravel-medialibrary/tests/TestSupport/testfiles/test%20with%20space.jpg';

    $media = $this->testModel
        ->addMediaFromUrl($url)
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory("{$media->id}/test-with-space.jpg"));
});

it('can add a remote file with an accent in the name to the media library', function () {
    $url = 'https://orbit.brightbox.com/v1/acc-jqzwj/Marquis-Leisure/reviews/images/000/000/898/original/Antar%C3%A8sThumb.jpg';

    $media = $this->testModel
        ->addMediaFromUrl($url)
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory("{$media->id}/AntarèsThumb.jpg"));
});

it('wil thrown an exception when a remote file could not be added', function () {
    $url = 'https://docs.spatie.be/images/medialibrary/thisonedoesnotexist.jpg';

    $this->expectException(UnreachableUrl::class);

    $this->testModel
        ->addMediaFromUrl($url)
        ->toMediaCollection();
});

it('wil throw an exception when a remote file has an invalid mime type', function () {
    $url = 'https://spatie.be/docs/laravel-medialibrary/v9/images/header.jpg';

    $this->expectException(MimeTypeNotAllowed::class);

    $this->testModel
        ->addMediaFromUrl($url, ['image/png'])
        ->toMediaCollection();
});

it('can rename the media before it gets added', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->usingName('othername')
        ->toMediaCollection();

    $this->assertEquals('othername', $media->name);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));
});

it('can rename the file before it gets added', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->usingFileName('othertest.jpg')
        ->toMediaCollection();

    $this->assertEquals('test', $media->name);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/othertest.jpg'));
});

it('will remove strange characters from the file name', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->usingFileName('other#test.jpg')
        ->toMediaCollection();

    $this->assertEquals('test', $media->name);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/other-test.jpg'));
});

it('will sanitize the file name using callable', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->sanitizingFileName(fn ($fileName) => 'new_file_name.jpg')
        ->toMediaCollection();

    $this->assertEquals('test', $media->name);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/new_file_name.jpg'));
});

test('the file name can be modified using a file namer', function () {
    config()->set('media-library.file_namer', RenameOriginalFileNamer::class);

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $this->assertEquals('test', $media->name);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/renamed_original_file.jpg'));
});

test('the file name can be modified using custom sanitizing and default file namer', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->usingFileName('other/otherFileName.jpg')
        ->sanitizingFileName(fn ($fileName) => strtolower(str_replace(['#', '\\', ' '], '-', $fileName)))
        ->toMediaCollection();

    $this->assertEquals('test', $media->name);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/other/otherfilename.jpg'));
});

test('the file name can be modified using custom sanitizing and default file namer and especial chars', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->usingFileName('other/[0]-{0}-(0)-otherFile.Name.jpg')
        ->sanitizingFileName(fn ($fileName) => strtolower(str_replace(['#', '\\', ' '], '-', $fileName)))
        ->toMediaCollection();

    $this->assertEquals('test', $media->name);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/other/[0]-{0}-(0)-otherfile.name.jpg'));
});

it('can save media in the right order', function () {
    $media = [];
    foreach (range(0, 5) as $index) {
        $media[] = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $this->assertEquals($index + 1, $media[$index]->order_column);
    }
});

it('can add properties to the saved media', function () {
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
});

it('can add manipulations to the saved media', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withManipulations(['thumb' => ['width' => '10']])
        ->toMediaCollection();

    $this->assertEquals('10', $media->manipulations['thumb']['width']);
});

it('can add file to model with morph map', function () {
    $media = $this->testModelWithMorphMap
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $this->assertEquals($this->testModelWithMorphMap->getMorphClass(), $media->model_type);
});

it('will throw an exception when setting the file to a wrong type', function () {
    $wrongType = [];

    $this->expectException(UnknownType::class);

    $this->testModel
        ->addMedia($this->getTestJpg())
        ->setFile($wrongType);
});

it('will throw an exception when adding a file that is too big', function () {
    app()['config']->set('media-library.max_file_size', 1);

    $this->expectException(FileIsTooBig::class);

    $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();
});

it('will throw an exception when adding a file to a non existing disk', function () {
    $this->expectException(DiskDoesNotExist::class);

    $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('images', 'non-existing-disk');
});

it('can add a base64 encoded file to the media library', function () {
    $testFile = $this->getTestJpg();
    $testBase64Data = base64_encode(file_get_contents($testFile));

    $media = $this->testModel
        ->addMediaFromBase64($testBase64Data)
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
});

test('a string can be accepted to be added to the media library', function () {
    $string = 'test123';

    $media = $this->testModel
        ->addMediaFromString($string)
        ->toMediaCollection();

    $this->assertEquals($string, file_get_contents($media->getPath()));
});

test('a stream can be accepted to be added to the media library', function () {
    $string = 'test123';
    $stream = fopen('php://temp', 'w+');
    fwrite($stream, $string);
    rewind($stream);

    $media = $this->testModel
        ->addMediaFromStream($stream)
        ->toMediaCollection();

    $this->assertEquals($string, file_get_contents($media->getPath()));
});

it('can add data uri prefixed base64 encoded file to the medialibrary', function () {
    $testFile = $this->getTestJpg();
    $testBase64Data = 'data:image/jpg;base64,'.base64_encode(file_get_contents($testFile));

    $media = $this->testModel
        ->addMediaFromBase64($testBase64Data)
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/'.$media->file_name));
});

it('will throw an exception when adding invalid base64 data', function () {
    $testFile = $this->getTestJpg();
    $invalidBase64Data = file_get_contents($testFile);

    $this->expectException(InvalidBase64Data::class);

    $this->testModel
        ->addMediaFromBase64($invalidBase64Data)
        ->toMediaCollection();
});

it('will throw an exception when adding invalid base64 mime type', function () {
    $testFile = $this->getTestJpg();
    $testBase64Data = base64_encode(file_get_contents($testFile));

    $this->expectException(MimeTypeNotAllowed::class);

    $this->testModel
        ->addMediaFromBase64($testBase64Data, ['image/png'])
        ->toMediaCollection();
});

it('can add files on an unsaved model even if not preserving the original', function () {
    $this->testUnsavedModel->name = 'test';

    $this->testUnsavedModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->testUnsavedModel->addMedia($this->getTestPdf())->toMediaCollection();

    $this->testUnsavedModel->save();

    $media = $this->testUnsavedModel->getMedia();

    $this->assertCount(2, $media);
});

it('can add an upload to the media library using dot notation', function () {
    app()['router']->get('/upload', function () {
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

    $result = $this->call('get', 'upload', [], [], ['file' => ['name' => $fileUpload]]);

    $this->assertEquals(200, $result->getStatusCode());
});
