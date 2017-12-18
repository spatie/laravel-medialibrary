<?php

namespace Tests\Unit\Rules;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;
use Illuminate\Support\Facades\Session;
use Spatie\MediaLibrary\Uploads\Rules\TemporaryUploadId;

class TemporaryUploadIdTest extends TestCase
{
    /** @var \App\Models\User */
    protected $user;

    /** @var \Spatie\MediaLibrary\Uploads\Models\TemporaryUpload */
    protected $temporaryUpload;

    /** @var \Spatie\MediaLibrary\Uploads\Rules\TemporaryUploadId */
    protected $rule;

    public function setUp()
    {
        parent::setUp();

        $fakeUpload = UploadedFile::fake()->image('test.jpg');

        $this->temporaryUpload = TemporaryUpload::createForFile(
            $fakeUpload,
            1,
            '/upload'
        );

        $this->rule = new TemporaryUploadId();
    }

    /** @test */
    public function it_succeed_when_the_id_is_valid_an_the_current_session_matches_the_session_on_the_temporary_upload()
    {
        Session::shouldReceive('getId')->andReturn(1);

        $this->assertTrue($this->rule->passes('upload_id', $this->temporaryUpload->id));
    }

    /** @test */
    public function it_fails_when_the_id_is_valid_an_the_current_session_does_not_match_the_session_on_the_temporary_upload()
    {
        Session::shouldReceive('getId')->andReturn(2);

        $this->assertFalse($this->rule->passes('upload_id', $this->temporaryUpload->id));
    }

    /** @test */
    public function it_fails_when_the_id_is_invalid_an_the_current_session_matches_the_session_on_the_temporary_upload()
    {
        Session::shouldReceive('getId')->andReturn(1);

        $this->assertFalse($this->rule->passes('upload_id', 123));
    }
}
