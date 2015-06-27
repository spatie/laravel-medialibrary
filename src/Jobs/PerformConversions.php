<?php

namespace Spatie\MediaLibrary\Jobs;

use App\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\MediaLibraryFileManipulator;

class SendReminderEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $conversions;

    protected $media;

    public function __construct(ConversionCollection $conversions, Media $media)
    {
        $this->conversions = $conversions;

        $this->media = $media;
    }

    public function handle()
    {
        app(MediaLibraryFileManipulator::class)->performConversions($this->conversions, $this->media);

        return true;
    }
}