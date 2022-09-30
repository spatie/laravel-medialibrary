<?php

use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\Mail;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\Mail\AttachmentMail;
use Spatie\MediaLibrary\Tests\TestSupport\Mail\InvalidMediaConversionAttachmentMail;
use Spatie\MediaLibrary\Tests\TestSupport\Mail\MediaConversionAttachmentMail;

beforeEach(function () {
    $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();
});

it('can create a mail attachment from a media', function () {
    $mailAttachment = Media::first()->toMailAttachment();

    expect($mailAttachment)->toBeInstanceOf(Attachment::class);
});

it('can send a mail with a media attached', function () {
    $mailable = new AttachmentMail(Media::first());

    Mail::send($mailable);

    // assert no exceptions thrown
    expect(true)->toBeTrue();
});

it('can create an attachment to a conversion', function () {
    $mailAttachment = $this->testModelWithConversion->getFirstMedia()->mailAttachment('thumb');

    expect($mailAttachment)->toBeInstanceOf(Attachment::class);
});

it('can send a mail with conversion attached', function () {
    $mailable = new MediaConversionAttachmentMail(Media::first());

    Mail::send($mailable);

    // assert no exceptions thrown
    expect(true)->toBeTrue();
});

it('will throw an exception when attaching a media specifying a non-existing conversion', function () {
    $mailable = new InvalidMediaConversionAttachmentMail(Media::first());

    Mail::send($mailable);
})->throws(InvalidConversion::class);
