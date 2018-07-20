<?php

namespace Spatie\MediaLibrary\Tests\Feature\Uploads\Commands;

use Carbon\Carbon;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;

class DeleteOldTemporaryUploadsTest extends TestCase
{
    /** @test */
    public function it_will_remove_old_temporary_uploads()
    {
        foreach (range(1, 5) as $index) {
            TemporaryUpload::create([
                'session_id' => rand(),
            ]);
        }

        $firstTemporaryUpload = TemporaryUpload::first();

        $firstTemporaryUpload->created_at = Carbon::now()->subDay(1);
        $firstTemporaryUpload->save();

        $this->artisan('medialibrary:delete-old-temporary-uploads');

        $this->assertCount(4, TemporaryUpload::all());
        $this->assertNull(TemporaryUpload::find($firstTemporaryUpload->id));
    }
}
