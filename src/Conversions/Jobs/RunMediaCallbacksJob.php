<?php

namespace Spatie\MediaLibrary\Conversions\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class RunMediaCallbacksJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    /**
     * @param  array<int, object>  $derivativeJobs
     */
    public function __construct(
        protected array $derivativeJobs,
        protected ?SerializableClosure $thenCallback,
        protected ?SerializableClosure $catchCallback,
        protected Media $media,
    ) {}

    public function handle(): void
    {
        try {
            foreach ($this->derivativeJobs as $derivativeJob) {
                app()->call([$derivativeJob, 'handle']);
            }

            if ($this->derivativeJobs !== []) {
                $this->media->refresh();
            }

            if ($this->thenCallback) {
                ($this->thenCallback->getClosure())($this->media);
            }
        } catch (Throwable $exception) {
            if (! $this->catchCallback) {
                throw $exception;
            }

            ($this->catchCallback->getClosure())($exception);
        }
    }
}
