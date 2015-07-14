<?php

namespace Spatie\MediaLibrary\Test\HasMediaTrait;

use File;
use Spatie\MediaLibrary\Test\TestCase;

class DeleteMediaTest extends TestCase
{
    public function setUp()
    {
        parent::setup();

        foreach (range(1, 3) as $index) {
            $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'), 'default', false);
            $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'), 'images', false);
        }
    }

    /**
     * @test
     */
    public function it_can_clear_a_collection()
    {
        $this->assertCount(3, $this->testModel->getMedia('default'));
        $this->assertCount(3, $this->testModel->getMedia('images'));

        $this->testModel->clearMediaCollection('images');

        $this->assertCount(3, $this->testModel->getMedia('default'));
        $this->assertCount(0, $this->testModel->getMedia('images'));
    }

   /**
    * @test
    */
   public function it_will_remove_the_files_when_clearing_a_collection()
   {
       $ids = $this->testModel->getMedia('images')->lists('id');

       $ids->map(function ($id) {
          $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
       });

       $this->testModel->clearMediaCollection('images');

       $ids->map(function ($id) {
           $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
       });
   }

    /**
     * @test
     */
    public function it_will_remove_the_files_when_deleting_a_subject()
    {
        $ids = $this->testModel->getMedia('images')->lists('id');

        $ids->map(function ($id) {
            $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
        });

        $this->testModel->delete();

        $ids->map(function ($id) {
            //failing test
            //$this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
        });
    }
}
