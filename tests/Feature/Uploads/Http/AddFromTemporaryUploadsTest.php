<?php

namespace Spatie\MediaLibrary\Tests\Feature\Uploads\Http;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\FileAdder\FileAdder;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;

class AddFromTemporaryUploadsTest extends TestCase
{
    /** @var \Illuminate\Support\Collection */
    protected $temporaryUploads;

    public function setUp(): void
    {
        parent::setUp();

        $this->temporaryUploads = collect();

        $this->temporaryUploads[] = TemporaryUpload::createForFile(UploadedFile::fake()->image('test-A.jpg'), session()->getId());

        $this->temporaryUploads[] = TemporaryUpload::createForFile(UploadedFile::fake()->image('test-B.jpg'), session()->getId());

        Route::post('add-from-temporary-uploads', function () {
            $this->testModel
                ->addMediaFromTemporaryUploads('files')
                ->each(function (FileAdder $fileAdder) {
                    $fileAdder->toMediaCollection();
                });
        });
    }

    /** @test */
    public function it_can_process_a_valid_temporary_upload_id()
    {
        $this->withoutExceptionHandling();

        $this->assertCount(2, TemporaryUpload::all());
        $this->assertCount(0, $this->testModel->getMedia());

        $response = $this
            ->post('add-from-temporary-uploads', [
                'files' => $this->temporaryUploads->map(function (TemporaryUpload $temporaryUpload) {
                    return ['upload_id' => $temporaryUpload->upload_id, 'name' => $temporaryUpload->upload_id];
                }),
            ]);

        $response->assertSuccessful();

        $this->testModel->refresh();

        $this->assertCount(0, TemporaryUpload::all());
        $this->assertCount(2, $this->testModel->getMedia());
    }
}
