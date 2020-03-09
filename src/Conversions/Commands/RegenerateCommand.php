<?php

namespace Spatie\MediaLibrary\Conversions\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RegenerateCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'media-library:regenerate {modelType?} {--ids=*}
    {--only=* : Regenerate specific conversions}
    {--only-missing : Regenerate only missing conversions}
    {--force : Force the operation to run when in production}';

    protected $description = 'Regenerate the derived images of media';

    protected MediaRepository $mediaRepository;

    protected FileManipulator $fileManipulator;

    protected array $errorMessages = [];

    public function handle(MediaRepository $mediaRepository, FileManipulator $fileManipulator)
    {
        $this->mediaRepository = $mediaRepository;

        $this->fileManipulator = $fileManipulator;

        if (! $this->confirmToProceed()) {
            return;
        }

        $mediaFiles = $this->getMediaToBeRegenerated();

        $progressBar = $this->output->createProgressBar($mediaFiles->count());

        $mediaFiles->each(function (Media $media) use ($progressBar) {
            try {
                $this->fileManipulator->createDerivedFiles(
                    $media,
                    Arr::wrap($this->option('only')),
                    $this->option('only-missing'),
                );
            } catch (Exception $exception) {
                $this->errorMessages[$media->getKey()] = $exception->getMessage();
            }

            $progressBar->advance();
        });

        $progressBar->finish();

        if (count($this->errorMessages)) {
            $this->warn('All done, but with some error messages:');

            foreach ($this->errorMessages as $mediaId => $message) {
                $this->warn("Media id {$mediaId}: `{$message}`");
            }
        }

        $this->info('All done!');
    }

    public function getMediaToBeRegenerated(): Collection
    {
        $modelType = $this->argument('modelType') ?? '';
        $mediaIds = $this->getMediaIds();

        if ($modelType === '' && count($mediaIds) === 0) {
            return $this->mediaRepository->all();
        }

        if (! count($mediaIds)) {
            return $this->mediaRepository->getByModelType($modelType);
        }

        return $this->mediaRepository->getByIds($mediaIds);
    }

    protected function getMediaIds(): array
    {
        $mediaIds = $this->option('ids');

        if (! is_array($mediaIds)) {
            $mediaIds = explode(',', $mediaIds);
        }

        if (count($mediaIds) === 1 && Str::contains($mediaIds[0], ',')) {
            $mediaIds = explode(',', $mediaIds[0]);
        }

        return $mediaIds;
    }
}
