<?php

namespace Spatie\MediaLibrary\Conversions\Commands;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Console\ConfirmableTrait;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\Conversions\FileResponsiveImageManipulator;

class RegenerateCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'media-library:regenerate {modelType?} {--ids=*}
    {--responsive-only : Regenerate responsive images only}
    {--only=* : Regenerate specific conversions (or collection_name for responsive images)}
    {--only-missing : Regenerate only missing conversions/responsive images}
    {--force : Force the operation to run when in production}';

    protected $description = 'Regenerate the derived images of media';

    protected MediaRepository $mediaRepository;

    protected FileManipulator $fileManipulator;

    protected FileResponsiveImageManipulator $fileRIManipulator;

    protected array $errorMessages = [];

    public function handle(MediaRepository $mediaRepository, FileManipulator $fileManipulator, FileResponsiveImageManipulator $fileRIManipulator)
    {
        $this->mediaRepository = $mediaRepository;

        $this->fileManipulator = $fileManipulator;

        $this->fileRIManipulator = $fileRIManipulator;

        if (! $this->confirmToProceed()) {
            return;
        }

        // options
        $only = Arr::wrap($this->option('only'));
        $missing = $this->option('only-missing');

        // files
        $mediaFiles = $this->getMediaToBeRegenerated();

        $progressBar = $this->output->createProgressBar($mediaFiles->count());

        // process files
        $mediaFiles->each(function (Media $media) use ($progressBar, $only, $missing) {
            try {
                if ($this->option('responsive-only')) {
                    $this->fileRIManipulator->createDerivedFiles(
                        $media,
                        $only,
                        $missing
                    );
                } else {
                    $this->fileManipulator->createDerivedFiles(
                        $media,
                        $only,
                        $missing,
                    );
                }
            } catch (Exception $exception) {
                $this->errorMessages[$media->getKey()] = $exception->getMessage();
            }

            $progressBar->advance();
        });

        $progressBar->finish();

        // errors
        if (count($this->errorMessages)) {
            $this->warn('All done, but with some error messages:' . PHP_EOL);

            foreach ($this->errorMessages as $mediaId => $message) {
                $this->warn("Media id {$mediaId}: `{$message}`" . PHP_EOL);
            }
        }

        // finish
        $this->info(PHP_EOL . 'All done!');
    }

    /**
     * filter media by type.
     */
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

    /**
     * filter media by id.
     */
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
