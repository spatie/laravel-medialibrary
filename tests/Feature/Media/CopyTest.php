<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class CopyTest extends TestCase
{
    /** @test */
    public function it_can_copy_media_from_one_model_to_another()
    {
        /** @var TestModel $model */
        $model = TestModel::create(['name' => 'test']);

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $model
            ->addMedia($this->getTestJpg())
            ->usingName('custom-name')
            ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $anotherModel = TestModel::create(['name' => 'another-test']);

        $movedMedia = $media->copy($anotherModel, 'images');

        $movedMedia->refresh();

        $this->assertCount(1, $model->getMedia('default'));
        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $this->assertCount(1, $anotherModel->getMedia('images'));
        $this->assertFileExists($this->getMediaDirectory($movedMedia->id.'/test.jpg'));
        $this->assertEquals($movedMedia->model->id, $anotherModel->id);
        $this->assertEquals($movedMedia->name, 'custom-name');
        $this->assertEquals($movedMedia->getCustomProperty('custom-property-name'), 'custom-property-value');
    }

    /** @test */
    public function it_can_copy_file_without_extension()
    {
        if (! file_exists(storage_path('media-library/temp'))) {
            mkdir(storage_path('media-library/temp'), 0777, true);
        }

        config(['media-library.temporary_directory_path' => realpath(storage_path('media-library/temp'))]);

        /** @var TestModel $model */
        $model = TestModel::create(['name' => 'test']);

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $model
            ->addMedia($this->getTestImageWithoutExtension())
            ->usingName('custom-name')
            ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/image'));

        $anotherModel = TestModel::create(['name' => 'another-test']);

        $movedMedia = $media->copy($anotherModel, 'images');

        $movedMedia->refresh();

        $this->assertCount(1, $model->getMedia('default'));
        $this->assertFileExists($this->getMediaDirectory($media->id.'/image'));

        $this->assertCount(1, $anotherModel->getMedia('images'));
        $this->assertFileExists($this->getMediaDirectory($movedMedia->id.'/image'));
        $this->assertEquals($movedMedia->model->id, $anotherModel->id);
        $this->assertEquals($movedMedia->name, 'custom-name');
        $this->assertEquals($movedMedia->getCustomProperty('custom-property-name'), 'custom-property-value');
    }

    /** @test */
    public function it_can_copy_media_from_one_model_to_another_on_a_specific_disk()
    {
        $diskName = 'secondMediaDisk';

        /** @var TestModel $model */
        $model = TestModel::create(['name' => 'test']);

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $model
            ->addMedia($this->getTestJpg())
            ->usingName('custom-name')
            ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $anotherModel = TestModel::create(['name' => 'another-test']);

        $movedMedia = $media->copy($anotherModel, 'images', $diskName);

        $movedMedia->refresh();

        $this->assertCount(1, $model->getMedia('default'));
        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $this->assertCount(1, $anotherModel->getMedia('images'));
        $this->assertFileExists($this->getTempDirectory('media2').'/'.$movedMedia->id.'/test.jpg');
        $this->assertEquals($movedMedia->collection_name, 'images');
        $this->assertEquals($movedMedia->disk, $diskName);
        $this->assertEquals($movedMedia->model->id, $anotherModel->id);
        $this->assertEquals($movedMedia->name, 'custom-name');
        $this->assertEquals($movedMedia->getCustomProperty('custom-property-name'), 'custom-property-value');
    }

    /** @test */
    public function it_can_copy_file_with_accent()
    {
        if (! file_exists(storage_path('media-library/temp'))) {
            mkdir(storage_path('media-library/temp'), 0777, true);
        }

        config(['media-library.temporary_directory_path' => realpath(storage_path('media-library/temp'))]);

        /** @var TestModel $model */
        $model = TestModel::create(['name' => 'test']);

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $model
            ->addMedia($this->getAntaresThumbJpgWithAccent())
            ->usingName('custom-name')
            ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/antarèsthumb.jpg'));

        $anotherModel = TestModel::create(['name' => 'another-test']);

        $movedMedia = $media->copy($anotherModel, 'images');

        $movedMedia->refresh();

        $this->assertCount(1, $model->getMedia('default'));
        $this->assertFileExists($this->getMediaDirectory($media->id.'/antarèsthumb.jpg'));

        $this->assertCount(1, $anotherModel->getMedia('images'));
        $this->assertFileExists($this->getMediaDirectory($movedMedia->id.'/antarèsthumb.jpg'));
        $this->assertEquals($movedMedia->model->id, $anotherModel->id);
        $this->assertEquals($movedMedia->name, 'custom-name');
        $this->assertEquals($movedMedia->getCustomProperty('custom-property-name'), 'custom-property-value');
    }
}
