<?php

namespace Spatie\MediaLibrary\Tests\Feature\Uploads\Http;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;

class TemporaryUploadControllerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Route::temporaryUploads('temporary-uploads');
    }

    /** @test */
    public function it_can_accept_a_temporary_upload()
    {
        $this->withoutExceptionHandling();

        $this
            ->post('temporary-uploads', [
                'file' => UploadedFile::fake()->image('image.jpg', 600, 600),
            ])
            ->assertSuccessful();

        $this->assertCount(1, TemporaryUpload::get());

        $temporaryUpload = TemporaryUpload::first();

        $this->assertCount(1, $temporaryUpload->getMedia());

        $this->assertEquals(session()->getId(), $temporaryUpload->session_id);
    }
}
