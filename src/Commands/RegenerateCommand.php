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
    protected $signature = 'medialibrary:regenerate {modelType?}';

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

    public function __construct(MediaRepository $mediaRepository, FileManipulator $fileManipulator)
    {
        parent::__construct();

        $this->mediaRepository = $mediaRepository;
        $this->fileManipulator = $fileManipulator;
    }

    public function handle()
    {
        $this->getMediaToBeRegenerated()->map(function (Media $media) {
            $this->fileManipulator->createDerivedFiles($media);
            $this->info(sprintf('Media %s regenerated', $media->id));
        });

        $this->info('All done!');
    }

    public function getMediaToBeRegenerated()
    {
        if ($this->argument('modelType') == '') {
            return $this->mediaRepository->all();
        }

        return $this->mediaRepository->getByModelType($this->argument('modelType'));
    }
}
