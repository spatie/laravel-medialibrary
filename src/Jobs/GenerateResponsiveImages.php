<?php

namespace Spatie\MediaLibrary\Jobs;

use Illuminate\Bus\Queueable;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImageGenerator;

class GenerateResponsiveImages implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    /** @var \Spatie\MediaLibrary\Models\Media */
    protected $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function handle(): bool
    {
        app(ResponsiveImageGenerator::class)->generateResponsiveImages($this->media);

        return true;
    }
}
