<?php

namespace Spatie\MediaLibrary\Conversions\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RunMediaCallbacksJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(
        protected ?SerializableClosure $thenCallback,
        protected Media $media,
    ) {}

    public function handle(): void
    {
        if (! $this->thenCallback) {
            return;
        }

        ($this->thenCallback->getClosure())($this->media);
    }
}
