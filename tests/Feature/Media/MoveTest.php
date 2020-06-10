<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class MoveTest extends TestCase
{
    /** @test */
    public function it_can_move_media_from_one_model_to_another()
    {
        $model = TestModel::create(['name' => 'test']);

        $media = $model
            ->addMedia($this->getTestJpg())
            ->usingName('custom-name')
            ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $anotherModel = TestModel::create(['name' => 'another-test']);

        $movedMedia = $media->move($anotherModel, 'images');

        $this->assertCount(0, $model->getMedia('default'));
        $this->assertFileDoesNotExist($this->getMediaDirectory($media->id.'/test.jpg'));

        $this->assertCount(1, $anotherModel->getMedia('images'));
        $this->assertFileExists($this->getMediaDirectory($movedMedia->id.'/test.jpg'));
        $this->assertEquals($movedMedia->model->id, $anotherModel->id);
        $this->assertEquals($movedMedia->name, 'custom-name');
        $this->assertEquals($movedMedia->getCustomProperty('custom-property-name'), 'custom-property-value');
    }

    /** @test */
    public function it_can_move_media_from_one_model_to_another_on_a_specific_disk()
    {
        $diskName = 'secondMediaDisk';

        $model = TestModel::create(['name' => 'test']);

        $media = $model
            ->addMedia($this->getTestJpg())
            ->usingName('custom-name')
            ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
            ->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $anotherModel = TestModel::create(['name' => 'another-test']);

        $movedMedia = $media->move($anotherModel, 'images', $diskName);

        $this->assertCount(0, $model->getMedia('default'));
        $this->assertFileDoesNotExist($this->getMediaDirectory($media->id.'/test.jpg'));

        $this->assertCount(1, $anotherModel->getMedia('images'));
        $this->assertFileExists($this->getTempDirectory('media2').'/'.$movedMedia->id.'/test.jpg');
        $this->assertEquals($movedMedia->collection_name, 'images');
        $this->assertEquals($movedMedia->disk, $diskName);
        $this->assertEquals($movedMedia->model->id, $anotherModel->id);
        $this->assertEquals($movedMedia->name, 'custom-name');
        $this->assertEquals($movedMedia->getCustomProperty('custom-property-name'), 'custom-property-value');
    }
}
