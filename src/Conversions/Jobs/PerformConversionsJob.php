<?php

namespace Spatie\Medialibrary\Conversions\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\Conversions\ConversionCollection;
use Spatie\Medialibrary\Conversions\FileManipulator;
use Spatie\Medialibrary\MediaCollections\Models\Media;

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
        /** @var \Spatie\Medialibrary\Conversions\FileManipulator $fileManipulator */
        $fileManipulator = app(FileManipulator::class);

        $fileManipulator->performConversions($this->conversions, $this->media, $this->onlyMissing);

        return true;
    }
}
