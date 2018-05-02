<?php

namespace Spatie\MediaLibrary\Tests\Feature\Models\Media;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;

class CopyTest extends TestCase
{
    /** @test */
    public function it_can_copy_media_from_one_model_to_another()
    {
        /** @var TestModel $model */
        $model = TestModel::create(['name' => 'test']);

        /** @var \Spatie\MediaLibrary\Models\Media $media */
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
}
