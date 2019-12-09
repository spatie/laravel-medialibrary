<?php

namespace Spatie\MediaLibrary\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\Models\Media;

class PerformConversions implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    /** @var \Spatie\MediaLibrary\Conversion\ConversionCollection */
    protected $conversions;

    /** @var \Spatie\MediaLibrary\Models\Media */
    protected $media;

    /** @var bool */
    protected $onlyMissing;

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
