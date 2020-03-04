<?php

namespace Spatie\Medialibrary\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\Conversions\ConversionCollection;
use Spatie\Medialibrary\Conversions\FileManipulator;
use Spatie\Medialibrary\Models\Media;

class PerformConversionsJob implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    protected ConversionCollection $conversions;

    protected Media $media;

    protected bool $onlyMissing;

    public function __construct(ConversionCollection $conversions, Media $media, $onlyMissing = false)
    {
        $this->conversions = $conversions;

        $this->media = $media;

        $this->onlyMissing = $onlyMissing;
    }

    public function handle(): bool
    {
        app(FileManipulator::class)->performConversions($this->conversions, $this->media, $this->onlyMissing);

        return true;
    }
}
