<?php

namespace Spatie\MediaLibrary\MediaCollections\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Symfony\Component\Console\Output\OutputInterface;

class TransformPathCommand extends Command
{
    use ConfirmableTrait;

    /*
     * Artisan configuration
     */
    protected $signature = 'media-library:transform-path
    {sourceGeneratorClass : Name of the PathGenerator class used to for existing media},
    {targetGeneratorClass : Name of the PathGenerator class to generate new paths for media},
    {modelType? : Name of the model to include in processing},
    {collectionName? : Name of the collection to include in processing},
    {--dry-run : List files that will be moved (without moving them)},
    {--force : Force the operation to run when in production},
    {--rate-limit= : Limit the number of items processed per second},
    {--ignore-missing-source-files : Do not consider missing source files to be an error},
    {--ignore-existing-target-files : Allow moving/overwriting existing target files}';

    protected $description = 'Moves media stored based on the "sourceGeneratorClass"
    path generator to comply with the path scheme provided by "targetGeneratorClass" .';

    /*
     * Options
     */
    protected ?PathGenerator $sourceGenerator = null;
    protected ?PathGenerator $targetGenerator = null;
    protected ?string $modelType = null;
    protected ?string $collectionName = null;
    protected bool $isDryRun = false;
    protected bool $ignoreMissingSourceFiles = false;
    protected bool $ignoreExistingTargetFiles = false;
    protected int $rateLimit = 0;

    /*
     * Dependencies
     */
    protected MediaRepository $mediaRepository;
    protected Factory $fileSystem;

    /**
     * @return int 0 on success, 1 on error
     */
    public function handle(
        MediaRepository $mediaRepository,
        Factory $fileSystem,
    ): int {
        $this->mediaRepository = $mediaRepository;
        $this->fileSystem = $fileSystem;

        if (! $this->handleArguments() || ! $this->confirmToProceed()) {
            return 1;
        }

        $mediaItems = $this->getMediaItems();
        $itemsToProcess = $this->scan($mediaItems);
        if (! $this->validate($itemsToProcess)) {
            return 1;
        }

        $hadErrors = false;
        foreach ($itemsToProcess as $file) {
            if (! $this->processMediaFile($file)) {
                $hadErrors = true;
            }
        }

        if ($hadErrors) {
            $this->info('Path transformation complete');
        } else {
            $this->warn('Path transformation completed with errors; see above');
        }

        return (int)$hadErrors;
    }

    /**
     * Generates an identifier string that provides information on the Media object,
     * its associated model, and the disks storing the media/conversion. This information
     * appears in error/warning messages for troubleshooting purposes.
     *
     * @param Media $media
     * @return string
     */
    protected function generateMediaIdentifier(Media $media): string
    {
        return "#$media->id ({$media->model->name} #{$media->getKey()} $media->disk/$media->conversions_disk)";
    }

    /**
     * Validate command line options/arguments
     *
     * @return bool FALSE if an error/invalid command line argument was detected, TRUE otherwise
     */
    protected function handleArguments(): bool
    {
        if (
            ! $this->guardAgainstInvalidGeneratorClasses(
                $this->argument('sourceGeneratorClass'),
                $this->argument('targetGeneratorClass')
            )
        ) {
            return false;
        }
        $this->sourceGenerator = app($this->argument('sourceGeneratorClass'));
        $this->targetGenerator = app($this->argument('targetGeneratorClass'));

        $this->isDryRun = $this->option('dry-run');
        $this->ignoreMissingSourceFiles = $this->option('ignore-missing-source-files');
        $this->ignoreExistingTargetFiles = $this->option('ignore-existing-target-files');
        $this->rateLimit = (int) $this->option('rate-limit');

        $this->modelType = $this->argument('modelType');
        if (! $this->guardAgainstInvalidModelType()) {
            return false;
        }

        $this->collectionName = $this->argument('collectionName');

        return true;
    }

    protected function guardAgainstInvalidGeneratorClasses(
        string $sourceGeneratorClass,
        string $targetGeneratorClass
    ): bool {
        foreach (
            [
                'Source' => $sourceGeneratorClass,
                'Target' => $targetGeneratorClass,
            ] as $type => $className
        ) {
            if (! class_exists($className)) {
                $this->error("$type generator class '$className' not found");
                return false;
            }

            if (! is_subclass_of($className, PathGenerator::class)) {
                $this->error("$type generator class '$className' does not implement the PathGenerator interface");
                return false;
            }
        }

        if ($sourceGeneratorClass === $targetGeneratorClass) {
            $this->error("Source and target generator classes cannot be of the same type");
            return false;
        }

        return true;
    }

    protected function guardAgainstInvalidModelType(): bool
    {
        if (! $this->modelType) {
            return true;
        }

        if (! class_exists($this->modelType)) {
            $this->error("Model '$this->modelType' does not exist");
            return false;
        }

        if (! is_subclass_of($this->modelType, HasMedia::class)) {
            $this->error("Model '$this->modelType' does not implement the HasMedia interface");
            return false;
        }

        return true;
    }

    /** @return DbCollection<int, Media> */
    protected function getMediaItems(): DbCollection
    {
        if (is_string($this->modelType) && is_string($this->collectionName)) {
            return $this->mediaRepository->getByModelTypeAndCollectionName(
                $this->modelType,
                $this->collectionName
            );
        }

        if (is_string($this->modelType)) {
            return $this->mediaRepository->getByModelType($this->modelType);
        }

        if (is_string($this->collectionName)) {
            return $this->mediaRepository->getByCollectionName($this->collectionName);
        }

        return $this->mediaRepository->all();
    }

    /**
     *
     * @return Collection
     * @var DbCollection<int, Media> $items
     */
    protected function scan(DbCollection $items): Collection
    {
        return $items->map(function (Media $media) {
            return [
                'id' => $this->generateMediaIdentifier($media),
                'media' => $media,
                'status' => $this->checkFileStatus($media)
            ];
        });
    }

    protected function checkFileStatus(Media $media): array
    {
        $conversionCollection = ConversionCollection::createForMedia($media);
        $conversions = array_merge([null], $media->getMediaConversionNames());

        $mediaDisk = $this->fileSystem->disk($media->disk);
        $sourcePath = $this->sourceGenerator->getPath($media);
        $targetPath = $this->targetGenerator->getPath($media);

        $conversionsDisk = $this->fileSystem->disk($media->conversions_disk);
        $sourceConversionsPath = $this->sourceGenerator->getPathForConversions($media);
        $targetConversionsPath = $this->targetGenerator->getPathForConversions($media);

        $results = [];

        foreach ($conversions as $conversionName) {
            if (!$conversionName) {
                $sourcePathAndFilename = $sourcePath . basename($media->getPath());
                $targetPathAndFilename = $targetPath . basename($media->getPath());
                $disk = $mediaDisk;
            } else {
                $conversionFile = $conversionCollection
                    ->getByName($conversionName)
                    ->getConversionFile($media);
                $sourcePathAndFilename = $sourceConversionsPath . $conversionFile;
                $targetPathAndFilename = $targetConversionsPath . $conversionFile;
                $disk = $conversionsDisk;
            }

            // If source and target path are the same, we do not need to move
            // and we can skip this
            if ($sourcePathAndFilename === $targetPathAndFilename) {
                continue;
            }

            $results[] = [
                'name' => $conversionName,
                'source' => $sourcePathAndFilename,
                'source_exists' => $disk->exists($sourcePathAndFilename),
                'target' => $targetPathAndFilename,
                'target_exists' => $disk->exists($targetPathAndFilename)
            ];
        }

        return $results;
    }

    /**
     * Unless these checks are disabled, ensure that there are no files that match the
     * source path generator that are missing, and that there are no pre-existing files that
     * match the target generator that are already present. Files which have already been moved
     * (i.e. that exist under the target generator but not under the source) are not considered
     * to be a problem.
     *
     * @param Collection<int,array> $items
     * @return bool
     */
    protected function validate(Collection $items): bool
    {
        $foundProblems = false;

        foreach ($items as $item) {
            $identifier = $item['id'];

            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $this->showStatusReport($item);
            }

            foreach ($item['status'] as $fileInfo) {
                $foundProblems = $this->validateFile($identifier, $fileInfo) || $foundProblems;
            }
        }

        return ! $foundProblems;
    }

    protected function validateFile(string $identifier, array $fileInfo): bool
    {
        $foundProblems = false;

        // Check if the source file is missing, and the matching destination file does not
        // already exist (in case we are resuming an interrupted move operation).  For
        // conversions, a missing source file is not considered an error but it will
        // be flagged as a warning.
        if (
            ! $this->ignoreMissingSourceFiles
            && (! $fileInfo['source_exists'] && ! $fileInfo['target_exists'])
        ) {
            if (!$fileInfo['name']) {
                $this->error(
                    "$identifier: Source media is missing (${fileInfo['source']})"
                );
                $foundProblems = true;
            } else {
                $this->warn(
                    "$identifier: Source media for conversion '${fileInfo['name']}'"
                    . "is missing (${fileInfo['source']})"
                );
            }
        }

        // Warn if the destination file already exists, unless the source file is missing
        // (in case we are resuming an interrupted move operation).
        if (
            ! $this->ignoreExistingTargetFiles
            && ($fileInfo['target_exists'] && $fileInfo['source_exists'])
        ) {
            if (!$fileInfo['name']) {
                $this->error(
                    "$identifier: Target media already exists (${fileInfo['target']})"
                );
            } else {
                $this->error(
                    "$identifier: Target conversion '${fileInfo['name']}'"
                    . "already exists (${fileInfo['target']})"
                );
            }

            $foundProblems = true;
        }

        return $foundProblems;
    }


    protected function showStatusReport(array $item): void
    {
        $this->newLine(1);
        $this->info($item['id']);

        foreach ($item['status'] as $status) {
            $this->info("  " . ($status['name'] ?? 'Primary Media') . ":");
            $this->info("    Source: ${status['source']} (exists: " . ($status['source_exists'] ? "yes" : "no") . ")");
            $this->info("    Target: ${status['target']} (exists: " . ($status['target_exists'] ? "yes" : "no") . ")");
            if (!$status['source_exists'] && $status['target_exists']) {
                $this->info("    Status: Already moved");
            } elseif ($status['source_exists'] && !$status['target_exists']) {
                $this->info("    Status: To be moved");
            } elseif (!$status['source_exists'] && !$status['target_exists']) {
                $this->info("    Status: Source missing");
            } elseif ($status['source_exists'] && $status['target_exists']) {
                $this->info("    Status: Target to be replaced");
            }
        }

        $this->newLine(1);
    }



    protected function processMediaFile(array $itemData): bool
    {
        $identifier = $itemData['id'];
        $media = $itemData['media'];

        foreach ($itemData['status'] as $item) {
            $this->rateLimiter();

            $isConversion = !!$item['name'];
            $diskName = $isConversion ? $media->conversions_disk : $media->disk;
            $disk = $this->fileSystem->disk($diskName);

            $sourcePath = $item['source'];
            $targetPath = $item['target'];

            if (! $disk->exists($sourcePath)) {
                if ($this->ignoreMissingSourceFiles) {
                    // If source file does not exist, we cannot move it; consider this a success
                    return true;
                } else {
                    $this->error($identifier . ": Source file missing ($sourcePath) on disk $diskName");
                    return false;
                }
            }

            if ($disk->exists($targetPath)) {
                if (! $this->ignoreExistingTargetFiles) {
                    $this->error($identifier . ": Target file already exists ($targetPath) on disk $diskName");
                    return false;
                }

                $disk->delete($targetPath);
            }

            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $this->info($identifier . ": $sourcePath -> $targetPath ($diskName)");
            }

            if (! $this->isDryRun) {
                if (! $disk->move($sourcePath, $targetPath)) {
                    $this->error($identifier . ": Failed to move '$sourcePath' to '$targetPath' on disk $diskName");
                    return false;
                }
            }
        }

        return true;
    }

    protected function rateLimiter()
    {
        if ($this->rateLimit) {
            usleep((1 / $this->rateLimit) * 1_000_000 * 2);
        }
    }
}
