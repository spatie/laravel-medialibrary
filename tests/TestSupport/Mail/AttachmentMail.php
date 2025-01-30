<?php

namespace Programic\MediaLibrary\Tests\TestSupport\Mail;

use Illuminate\Mail\Mailable;
use Programic\MediaLibrary\MediaCollections\Models\Media;

class AttachmentMail extends Mailable
{
    public function __construct(public Media $media) {}

    public function build()
    {
        return $this
            ->to('johndoe@example.com')
            ->view('mailable')
            ->attach($this->media);
    }
}
