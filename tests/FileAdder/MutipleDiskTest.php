<?php

namespace Spatie\MediaLibrary\Test\HasMediaTrait;

use Spatie\MediaLibrary\Test\TestCase;

class MultipleDiskTest extends TestCase
{

/**
* @test
*/
public function it_can_add_a_file_to_a_named_collection_on_a_specific_disk()
{
$collectionName = 'images';
$diskName = 'secondMediaDisk';

$media = $this->testModel->addMedia($this->getTestJpg())->toCollectionOnDisk($collectionName, $diskName);

$this->assertEquals($collectionName, $media->collection_name);
$this->assertEquals($diskName, $media->disk);
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
}
}