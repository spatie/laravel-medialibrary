<?php

namespace Spatie\MediaLibrary\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\MediaRepository;

class RegenerateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'medialibrary:regenerate {modelType?} {--ids=*}';

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
        $this->getMediaToBeRegenerated()->map(function (Media $media) {
            $this->fileManipulator->createDerivedFiles($media);
            $this->info(sprintf('Media %s regenerated', $media->id));
        });

        $this->info('All done!');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMediaToBeRegenerated()
    {
        $modelType = $this->argument('modelType');
        $mediaIds = $this->option('ids');

        if ($modelType == '' && !$mediaIds) {
            return $this->mediaRepository->all();
        }

        if ($mediaIds) {
            return $this->mediaRepository->getByIds($mediaIds);
        }

        return $this->mediaRepository->getByModelType($modelType);
    }
}
