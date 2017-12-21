<?php

namespace Tests\Feature\Uploads\Http;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;
use Spatie\MediaLibrary\Uploads\Rules\TemporaryUploadId;

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
        $this
            ->post('temporary-uploads', [
                'file' => UploadedFile::fake()->image('image.jpg', 600, 600)
            ])
            ->assertSuccessful();

        $this->assertCount(1, TemporaryUpload::get());

        $temporaryUpload = TemporaryUpload::first();

        $this->assertCount(1, $temporaryUpload->getMedia());

        $this->assertNotEmpty($temporaryUpload->session_id);
    }
}
