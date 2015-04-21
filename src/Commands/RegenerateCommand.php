<?php namespace App\Services\MediaLibrary\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Spatie\MediaLibrary\MediaLibraryFacade as MediaLibrary;
use Spatie\MediaLibrary\Models\Media;

class RegenerateCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'medialibrary:regenerate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate the derived images of media';



    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $onlyForModel = ucfirst($this->argument('model'));

        foreach(Media::all() as $media) {
            if ($onlyForModel == '' || $onlyForModel == $media->content_type) {
                MediaLibrary::regenerateDerivedFiles($media);
                $this->info('Media id ' . $media->id .  ' "' . $media->path . '" reprocessed' . ' (for ' . $media->content_type . ' id ' . $media->content_id . ')');
            }
        }

        $this->info('All done!');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::OPTIONAL, 'Regenerate only for this model']
        ];
    }

}
