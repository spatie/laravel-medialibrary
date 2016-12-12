<?php

namespace Spatie\MediaLibrary\Commands;

use Exception;
use Spatie\MediaLibrary\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Console\ConfirmableTrait;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\MediaRepository;

class RegenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'medialibrary:regenerate {modelType?} {--ids=*}
    {-- force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate the derived images of media';

    /**
     * @var \Spatie\MediaLibrary\MediaRepository
     */
    protected $mediaRepository;

    /**
     * @var \Spatie\MediaLibrary\FileManipulator
     */
    protected $fileManipulator;

    /**
     * @var array
     */
    protected $erroredMediaIds = [];

    /**
     * RegenerateCommand constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param FileManipulator $fileManipulator
     */
    public function __construct(MediaRepository $mediaRepository, FileManipulator $fileManipulator)
    {
        parent::__construct();

        $this->mediaRepository = $mediaRepository;
        $this->fileManipulator = $fileManipulator;
    }

    /**
     * Handle regeneration.
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->getMediaToBeRegenerated()->each(function (Media $media) {
            try {
                $this->fileManipulator->createDerivedFiles($media);
                $this->info("Media {$media->id} regenerated");
            } catch (Exception $exception) {
                $this->error("Media {$media->id} could not be regenerated because `{$exception->getMessage()}`");
                $this->erroredMediaIds[] = $media->id;
            }
        });

        if (count($this->erroredMediaIds)) {
            $this->warn('The derived files of these media ids could not be regenerated: '.implode(',', $this->erroredMediaIds));
        }

        $this->info('All done!');
    }

    public function getMediaToBeRegenerated(): Collection
    {
        $modelType = $this->argument('modelType') ?? '';
        $mediaIds = $this->option('ids');

        if ($modelType === '' && ! $mediaIds) {
            return $this->mediaRepository->all();
        }

        if ($mediaIds) {
            if (! is_array($mediaIds)) {
                $mediaIds = explode(',', $mediaIds);
            }

            return $this->mediaRepository->getByIds($mediaIds);
        }

        return $this->mediaRepository->getByModelType($modelType);
    }
}
