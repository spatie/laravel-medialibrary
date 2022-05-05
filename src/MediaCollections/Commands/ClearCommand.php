<?php

namespace Spatie\MediaLibrary\MediaCollections\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ClearCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'media-library:clear {modelType?} {collectionName?}
    {-- force : Force the operation to run when in production}';

    protected $description = 'Delete all items in a media collection.';

    protected MediaRepository $mediaRepository;

    public function handle(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;

        if (! $this->confirmToProceed()) {
            return;
        }

        $mediaItems = $this->getMediaItems();

        $progressBar = $this->output->createProgressBar($mediaItems->count());

        $mediaItems->each(function (Media $media) use ($progressBar) {
            $media->delete();
            $progressBar->advance();
        });

        $progressBar->finish();

        $this->info('All done!');
    }

    /** @return Collection<int, Media> */
    public function getMediaItems(): Collection
    {
        $modelType = $this->argument('modelType');
        $collectionName = $this->argument('collectionName');

        if (is_string($modelType) && is_string($collectionName)) {
            return $this->mediaRepository->getByModelTypeAndCollectionName(
                $modelType,
                $collectionName
            );
        }

        if (is_string($modelType)) {
            return $this->mediaRepository->getByModelType($modelType);
        }

        if (is_string($collectionName)) {
            return $this->mediaRepository->getByCollectionName($collectionName);
        }

        return $this->mediaRepository->all();
    }
}
