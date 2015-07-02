<?php namespace Spatie\MediaLibrary;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaLibraryFacade as MediaLibrary;

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
     * @var \Spatie\MediaLibrary\Repository
     */
    protected $mediaRepository;
    /**
     * @var \Spatie\MediaLibrary\FileManipulator
     */
    protected $fileManipulator;

    public function __construct(MediaRepository $mediaRepository, FileManipulator $fileManipulator)
    {
        parent::__construct();

        $this->mediaLibraryRepository = $mediaRepository;
        $this->fileManipulator = $fileManipulator;
    }

    public function handle()
    {
        $this->getMediaToBeRegenerated()->map(function(Media $media) {
            $this->fileManipulator->createDerivedFiles($media);
            $this->info(sprintf('Media %s regenerated', $media->id));
        });

        $this->info('All done!');
    }

    public function getMediaToBeRegenerated()
    {
        if ($this->argument('modelType') == '')
        {
            return $this->mediaLibraryRepository->all();
        }

        return $this->mediaLibraryRepository->getByModelType($this->argument('modelType'));

    }

}
