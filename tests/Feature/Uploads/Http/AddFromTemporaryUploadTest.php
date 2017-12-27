<?php

namespace Tests\Feature\Uploads\Http;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;
use Spatie\MediaLibrary\Uploads\Rules\TemporaryUploadId;

class AddFromTemporaryUploadTest extends TestCase
{
    /** Spatie\MediaLibrary\Uploads\Models\TemporaryUpload */
    protected $temporaryUpload;

    public function setUp()
    {
        parent::setUp();

        $fakeUpload = UploadedFile::fake()->image('test.jpg');

        Session::shouldReceive('getId')->andReturn(1);

        $this->temporaryUpload = TemporaryUpload::createForFile(
            $fakeUpload,
            1
        );

        Route::post('add-from-temporary-upload', function () {
            $this->testModel
                ->addMediaFromTemporaryUpload('files')
                ->toMediaCollection();
        });
    }

    /** @test */
    public function it_can_process_a_valid_temporary_upload_id()
    {
        $this->assertCount(1, TemporaryUpload::all());
        $this->assertCount(0, $this->testModel->getMedia());

        $this
            ->post('add-from-temporary-upload', [
                'files' => [
                    ['id' => $this->temporaryUpload->id, 'name' => 'my-name'],
                ],
            ])
            ->assertSuccessful();

        $this->testModel->refresh();

        $this->assertCount(0, TemporaryUpload::all());
        $this->assertCount(1, $this->testModel->getMedia());
    }
}
