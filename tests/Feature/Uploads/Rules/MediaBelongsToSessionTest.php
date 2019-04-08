<?php

namespace Spatie\MediaLibrary\Tests\Feature\Uploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;
use Spatie\MediaLibrary\Uploads\Rules\MediaBelongsToSession;

class MediaBelongsToSessionTest extends TestCase
{
    /** @var \Spatie\MediaLibrary\Uploads\Models\TemporaryUpload */
    protected $temporaryUpload;

    /** @var \Spatie\MediaLibrary\Uploads\Rules\MediaBelongsToSession */
    protected $rule;

    public function setUp(): void
    {
        parent::setUp();

        $fakeUpload = UploadedFile::fake()->image('test.jpg');

        $this->temporaryUpload = TemporaryUpload::createForFile(
            $fakeUpload,
            1
        );

        $this->rule = new MediaBelongsToSession();
    }

    /** @test */
    public function it_will_succeed_if_the_media_belongs_to_the_session()
    {
        Session::shouldReceive('getId')->andReturn(1);

        $this->assertTrue($this->rule->passes('upload_id', $this->temporaryUpload->getFirstMedia()->id));
    }

    /** @test */
    public function it_will_not_succeed_if_the_media_does_not_belong_to_the_session()
    {
        Session::shouldReceive('getId')->andReturn(2);

        $this->assertFalse($this->rule->passes('upload_id', $this->temporaryUpload->getFirstMedia()->id));
    }
}
