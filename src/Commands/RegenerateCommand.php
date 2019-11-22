<?php

namespace Spatie\MediaLibrary\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\MediaRepository;
use Spatie\MediaLibrary\Models\Media;

class RegenerateCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'medialibrary:regenerate {modelType?} {--ids=*}
    {--only=* : Regenerate specific conversions}
    {--only-missing : Regenerate only missing conversions}
    {--force : Force the operation to run when in production}';

    protected $description = 'Regenerate the derived images of media';

    /** @var \Spatie\MediaLibrary\MediaRepository */
    protected $mediaRepository;

    /** @var \Spatie\MediaLibrary\FileManipulator */
    protected $fileManipulator;

    /** @var array */
    protected $erroredMediaIds = [];

    public function __construct(MediaRepository $mediaRepository, FileManipulator $fileManipulator)
    {
        parent::__construct();

        $this->mediaRepository = $mediaRepository;
        $this->fileManipulator = $fileManipulator;
    }

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $mediaFiles = $this->getMediaToBeRegenerated();

        $progressBar = $this->output->createProgressBar($mediaFiles->count());

        $this->errorMessages = [];

        $mediaFiles->each(function (Media $media) use ($progressBar) {
            try {
                $this->fileManipulator->createDerivedFiles(
                    $media,
                    Arr::wrap($this->option('only')),
                    $this->option('only-missing')
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
