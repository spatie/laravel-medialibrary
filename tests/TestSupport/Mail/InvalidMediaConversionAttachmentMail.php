<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\Mail;

use Illuminate\Mail\Mailable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class InvalidMediaConversionAttachmentMail extends Mailable
{
    public function __construct(public Media $media) {}

    public function build()
    {
        $thumbnailAttachment = $this->media->mailAttachment('non-existing-conversion');

        return $this
            ->to('johndoe@example.com')
            ->view('mailable')
            ->attach($thumbnailAttachment);
    }
}
