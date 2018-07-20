<?php

namespace Spatie\MediaLibrary\Tests\Feature\Uploads\Http;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;

class AddFromTemporaryUploadTest extends TestCase
{
    /** Spatie\MediaLibrary\Uploads\Models\TemporaryUpload */
    protected $temporaryUpload;

    public function setUp()
    {
        parent::setUp();

        $fakeUpload = UploadedFile::fake()->image('test.jpg');

        $this->temporaryUpload = TemporaryUpload::createForFile(
            $fakeUpload,
            session()->getId()
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
        $this->withoutExceptionHandling();

        $this->assertCount(1, TemporaryUpload::all());
        $this->assertCount(0, $this->testModel->getMedia());

        $response = $this
            ->post('add-from-temporary-upload', [
                'files' => [
                    ['id' => $this->temporaryUpload->id, 'name' => 'my-name'],
                ],
            ]);

        $response->assertSuccessful();

        $this->testModel->refresh();

        $this->assertCount(0, TemporaryUpload::all());
        $this->assertCount(1, $this->testModel->getMedia());
    }
}
