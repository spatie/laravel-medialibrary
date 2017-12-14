<?php

namespace Spatie\MediaLibrary\Tests\Models;

use Spatie\MediaLibrary\Tests\TestCase;

use \Spatie\MediaLibrary\Models\TemporaryUpload;
use Illuminate\Http\UploadedFile;

class TemporaryUploadTest
{
    /** @var \Spatie\MediaLibrary\Models\TemporaryUpload */
    protected $temporaryUpload;

    /** @var \App\Models\User */
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->temporaryUpload = TemporaryUpload::createForFile(
            UploadedFile::fake()->image('test.jpg'),
            $this->user
        );
    }

    /** @test */
    public function it_can_be_created_for_a_file_and_and_a_user()
    {
        $this->assertCount(1, $this->temporaryUpload->getMedia());

        $this->assertEquals($this->user->id, $this->temporaryUpload->user->id);
    }

    /** @test */
    public function its_media_can_be_transfered_to_another_model()
    {
        $artwork = factory(Artwork::class)->create();

        $this->assertCount(0, $artwork->images);
        $this->assertCount(1, TemporaryUpload::all());

        $this->temporaryUpload->transferTo($artwork, 'images');

        $this->assertCount(1, $artwork->fresh()->images);
        $this->assertCount(0, TemporaryUpload::all());
    }

    /** @test */
    public function it_can_be_found_by_id_and_user()
    {
        $temporaryUpload = TemporaryUpload::findById($this->temporaryUpload->id, $this->user);

        $this->assertEquals($this->temporaryUpload->id, $temporaryUpload->id);
        $this->assertEquals($this->user->id, $temporaryUpload->user->id);

        $this->assertNull(TemporaryUpload::findById('123', $this->user));

        $otherUser = factory(User::class)->create();
        $this->assertNull(TemporaryUpload::findById($this->temporaryUpload->id, $otherUser));
    }
}
