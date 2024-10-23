<?php

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\FileRemover\FileBaseFileRemover;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Tests\Support\PathGenerator\CustomDirectoryStructurePathGenerator;
use Spatie\MediaLibrary\Tests\TestSupport\TestCustomPathGenerator;
use Spatie\MediaLibrary\Tests\TestSupport\TestFileNamer;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestPathGenerator;

it('will remove the files when deleting an object that has media', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeTrue();

    $this->testModel->delete();

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeFalse();
});

it('will remove the files when deleting a media instance', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeTrue();

    $media->delete();

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeFalse();
});

it('will remove the files without extension', function () {
    $media = $this->testModel->addMedia($this->getTestImageWithoutExtension())->toMediaCollection('images');

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeTrue();

    $media->delete();

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeFalse();
});

it('will remove files when deleting a media object with a custom path generator', function () {
    config(['media-library.path_generator' => TestPathGenerator::class]);

    $pathGenerator = new TestPathGenerator;

    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');
    $path = $pathGenerator->getPath($media);

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeTrue();

    $this->testModel->delete();

    expect(File::isDirectory($this->getTempDirectory($path)))->toBeFalse();
});

it('will remove files when deleting a media object with a custom path and directory generator', function () {
    config(['media-library.path_generator' => CustomDirectoryStructurePathGenerator::class]);
    config(['media-library.file_remover_class' => FileBaseFileRemover::class]);

    $pathGenerator = new CustomDirectoryStructurePathGenerator;

    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');
    $path = $pathGenerator->getPath($media);

    expect(File::exists($media->getPath()))->toBeTrue();

    $this->testModel->delete();

    expect(File::exists($media->getPath()))->toBeFalse();
});

it('will remove converted files when deleting a media object with a custom path and directory generator and custom removal class', function () {
    config(['media-library.path_generator' => CustomDirectoryStructurePathGenerator::class]);
    config(['media-library.file_remover_class' => FileBaseFileRemover::class]);

    $pathGenerator = new CustomDirectoryStructurePathGenerator;

    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect(File::exists($media->getPath()))->toBeTrue();
    expect(File::exists($media->getPath('thumb')))->toBeTrue();
    expect(File::exists($media->getPath('keep_original_format')))->toBeTrue();

    $media->delete();

    expect(File::exists($media->getPath()))->toBeFalse();
    expect(File::exists($media->getPath('thumb')))->toBeFalse();
    expect(File::exists($media->getPath('keep_original_format')))->toBeFalse();
});

it('will remove converted files and responsive images when deleting a media object with a custom path and directory generator and custom removal class', function () {
    config(['media-library.path_generator' => CustomDirectoryStructurePathGenerator::class]);
    config(['media-library.file_remover_class' => FileBaseFileRemover::class]);

    $media = $this->testModelWithConversionsOnOtherDisk->addMedia($this->getTestPng())->toMediaCollection('images');
    $pathGenerator = new CustomDirectoryStructurePathGenerator;

    expect(File::exists($media->getPath()))->toBeTrue();
    expect(Storage::disk($media->disk)->exists($pathGenerator->getPathForResponsiveImages($media).'test___thumb_50_63.jpg'))->toBeTrue();

    $media->delete();

    expect(File::exists($media->getPath()))->toBeFalse();
    expect(Storage::disk($media->disk)->exists($pathGenerator->getPathForResponsiveImages($media).'test___thumb_50_63.jpg'))->toBeFalse();

});

it('will NOT remove other files within the same folder when deleting a media object with a custom path and directory generator', function () {
    config(['media-library.path_generator' => CustomDirectoryStructurePathGenerator::class]);
    config(['media-library.file_remover_class' => FileBaseFileRemover::class]);

    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');
    $media2 = $this->testModel->addMedia($this->getTestPng())->toMediaCollection('images');

    expect(File::exists($media->getPath()))->toBeTrue();
    expect(File::exists($media2->getPath()))->toBeTrue();

    $media->delete();

    expect(File::exists($media->getPath()))->toBeFalse();
    expect(File::exists($media2->getPath()))->toBeTrue();
});

it('will NOT remove other files within the same folder when deleting a media object with similar image names saved on same custom path and directory generator', function () {

    config(['media-library.path_generator' => TestCustomPathGenerator::class]);
    config(['media-library.file_remover_class' => FileBaseFileRemover::class]);

    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');
    $media2 = $this->testModel->addMedia($this->getTestImageEndingWithUnderscore())->toMediaCollection('images');

    expect(File::exists($media->getPath()))->toBeTrue();
    expect(File::exists($media2->getPath()))->toBeTrue();

    $media->delete();

    expect(File::exists($media->getPath()))->toBeFalse();
    expect(File::exists($media2->getPath()))->toBeTrue();
});

it('will remove conversion files when using custom file namer', function () {
    config(['media-library.path_generator' => DefaultPathGenerator::class]);
    config(['media-library.file_namer' => TestFileNamer::class]);

    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect(File::exists($media->getPath('thumb')))->toBeTrue();
    expect(File::exists($media->getPath('keep_original_format')))->toBeTrue();

    $media->delete();

    expect(File::exists($media->getPath()))->toBeFalse();
    expect(File::exists($media->getPath('thumb')))->toBeFalse();
    expect(File::exists($media->getPath('keep_original_format')))->toBeFalse();
    expect(File::exists($this->getMediaDirectory($media->getKey()).'/conversions'))->toBeFalse();
});

it('will remove responsive images when using custom file namer', function () {
    config(['media-library.path_generator' => DefaultPathGenerator::class]);
    config(['media-library.file_namer' => TestFileNamer::class]);

    $media = $this->testModelWithResponsiveImages->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect(File::exists($this->getMediaDirectory($media->getKey()).'/conversions'))->toBeTrue();
    expect(File::exists($this->getMediaDirectory($media->getKey()).'/responsive-images'))->toBeTrue();
    expect(File::exists($this->getMediaDirectory($media->getKey())))->toBeTrue();


    $media->delete();

    expect(File::exists($media->getPath()))->toBeFalse();
    expect(File::exists($this->getMediaDirectory($media->getKey()).'/conversions'))->toBeFalse();
    expect(File::exists($this->getMediaDirectory($media->getKey()).'/responsive-images'))->toBeFalse();
    expect(File::exists($this->getMediaDirectory($media->getKey())))->toBeFalse();
});

it('will not remove the files when should delete preserving media returns true', function () {
    $testModelClass = new class extends TestModel
    {
        public function shouldDeletePreservingMedia(): bool
        {
            return true;
        }
    };

    $testModel = $testModelClass::find($this->testModel->id);

    $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    $testModel = $testModel->fresh();

    $testModel->delete();

    $this->assertNotNull(Media::find($media->id));
});

it('will remove the files when should delete preserving media returns false', function () {
    $testModelClass = new class extends TestModel
    {
        public function shouldDeletePreservingMedia(): bool
        {
            return false;
        }
    };

    $testModel = $testModelClass::find($this->testModel->id);

    $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    $testModel = $testModel->fresh();

    $testModel->delete();

    expect(Media::find($media->id))->toBeNull();
});

it('will not remove the file when model uses softdelete', function () {
    $testModelClass = new class extends TestModel
    {
        use SoftDeletes;
    };

    /** @var TestModel $testModel */
    $testModel = $testModelClass::find($this->testModel->id);

    $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeTrue();

    $testModel = $testModel->fresh();

    $testModel->delete();

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeTrue();
});

it('will remove the file when model uses softdelete with force', function () {
    $testModelClass = new class extends TestModel
    {
        use SoftDeletes;
    };

    /** @var TestModel $testModel */
    $testModel = $testModelClass::find($this->testModel->id);

    $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeTrue();

    $testModel = $testModel->fresh();

    $testModel->forceDelete();

    expect(File::isDirectory($this->getMediaDirectory($media->id)))->toBeFalse();
});
