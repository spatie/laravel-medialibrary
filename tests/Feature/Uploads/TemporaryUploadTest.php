<?php

namespace Spatie\MediaLibrary\Tests\Feature\Uploads;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;

class TemporaryUploadTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setNow(2017, 1, 1);
    }

    /** @test */
    public function it_has_a_scope_to_get_old_records()
    {
        TemporaryUpload::create([
            'session_id' => rand(),
        ]);

        $this->assertCount(1, TemporaryUpload::get());

        $this->progressTime((60 * 24) - 1);

        $this->assertCount(0, TemporaryUpload::old()->get());

        $this->progressTime(1);

        $this->assertCount(1, TemporaryUpload::old()->get());
    }
}
