<?php

namespace Spatie\MediaLibrary\Tests\Feature\Uploads;

use Spatie\MediaLibrary\Models\Media;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\ZipStreamResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;

class TemporaryUploadTest extends TestCase
{
    /** @test */
    public function it_has_a_scope_to_get_old_records()
    {
        $temporaryUpload = factory(TemporaryUpload::class)->create();

        $this->assertCount(0, TemporaryUpload::old()->get());

        $temporaryUpload->created_at = Carbon::now()->subHour(23);
        $temporaryUpload->save();

        $this->assertCount(0, TemporaryUpload::old()->get());

        $temporaryUpload->created_at = Carbon::now()->subDay();
        $temporaryUpload->save();

        $this->assertCount(1, TemporaryUpload::old()->get());
    }
}
