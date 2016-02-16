<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use Illuminate\Support\Facades\Artisan;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Test\TestModel;

class ClearCommandTest extends TestCase
{
    /**
     * @test
     */
    public function delete_model_medias()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toCollection('collection1');
        $this->assertFileExists(  $this->getMediaDirectory("{$media->id}/test.jpg") ) ;
        Artisan::call('medialibrary:clear');
        $this->assertFileNotExists( $this->getMediaDirectory("{$media->id}/test.jpg") ) ;
    }

    /**
     * @test
     */
    public function delete_model_and_collection_medias()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toCollection('collection');
        $this->assertFileExists(  $this->getMediaDirectory("{$media->id}/test.jpg") ) ;
        Artisan::call('medialibrary:clear' , [
            "modelType" => TestModel::class ,
            "collectionName" => "collection1" ,
        ]);
        $this->assertFileExists( $this->getMediaDirectory("{$media->id}/test.jpg") ) ;
        Artisan::call('medialibrary:clear' , [
            "modelType" => TestModel::class ,
            "collectionName" => "collection" ,
        ]);
        $this->assertFileNotExists( $this->getMediaDirectory("{$media->id}/test.jpg") ) ;
    }
}
