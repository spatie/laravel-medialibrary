<?php

namespace Spatie\MediaLibrary\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\MediaRepository;
use Spatie\MediaLibrary\Models\Media;

class ClearCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'medialibrary:clear {modelType?} {collectionName?}
    {-- force : Force the operation to run when in production}';

    protected $description = 'Delete all items in a media collection.';

    /** @var \Spatie\MediaLibrary\MediaRepository */
    protected $mediaRepository;

    public function __construct(MediaRepository $mediaRepository)
    {
        parent::__construct();
        $this->mediaRepository = $mediaRepository;
    }

    public function handle()
    {
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

        $this->info("All done!");
    }

    public function getMediaItems() : Collection
    {
        $modelType = $this->argument('modelType');
        $collectionName = $this->argument('collectionName');

        if (! is_null($modelType) && ! is_null($collectionName)) {
            return $this->mediaRepository->getByModelTypeAndCollectionName(
                $modelType,
                $collectionName
            );
        }

        if (! is_null($modelType)) {
            return $this->mediaRepository->getByModelType($modelType);
        }

        if (! is_null($collectionName)) {
            return $this->mediaRepository->getByCollectionName($collectionName);
        }

        return $this->mediaRepository->all();
    }
}
