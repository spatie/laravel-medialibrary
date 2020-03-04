<?php

namespace Spatie\Medialibrary\Features\ResponsiveImages\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\Features\MediaCollections\Models\Media;
use Spatie\Medialibrary\Features\ResponsiveImages\ResponsiveImageGenerator;

class GenerateResponsiveImagesJob implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    protected Media $media;

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
