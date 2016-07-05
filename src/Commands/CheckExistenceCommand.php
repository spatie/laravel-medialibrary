<?php

namespace Spatie\MediaLibrary\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaRepository;
use Spatie\MediaLibrary\Services\CheckExistence;

class CheckExistenceCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'medialibrary:checkExistence
        { --only= : A comma separated list of models to check for. }
        { --except= : A comma separated list of models to exclude from the check. }
        { --all : Check for the existence of all models without any prompts. }
        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for the existence of media files in storage.';

    /**
     * @var \Spatie\MediaLibrary\MediaRepository
     */
    protected $mediaRepository;

    /**
     * @var CheckExistence
     */
    protected $service;

    /**
     * RegenerateCommand constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param CheckExistence $service
     */
    public function __construct(MediaRepository $mediaRepository, CheckExistence $service)
    {
        parent::__construct();

        $this->mediaRepository = $mediaRepository;
        $this->service = $service;
    }

    /**
     * Handle regeneration.
     */
    public function handle()
    {
        $media = $this->getMediaToCheck();
        $generator = $this->service->handle($media['type'], $media['models']);
        $count = 0;
        $bar = null;

        foreach ($generator as $item) {
            if ($count === 0) {
                $bar = $this->output->createProgressBar($item);
                $count++;
                continue;
            }
            $bar->advance(1);
        }
        $bar->finish();
        $this->output->newLine(2);
        $items = $generator->getReturn();

        foreach ($items as $item) {
            $this->line("Media ID # {$item->id} could not be found on the disk.");
        }

        $this->output->newLine(2);

        if ($items->count() > 0) {
            $this->line('Some items were not found. Please review the log and address the errors.');
            $this->line("Total items not found: {$items->count()}");
            return;
        }
        $this->line('The check has completed and no issues have been found!');
    }

    /**
     * Get the media to check for existence based on user input.
     *
     * @return array
     */
    protected function getMediaToCheck() : array
    {
        $output = [
            'type' => 'none',
            'models' => null
        ];

        if ($this->option('all')) {
            return $output;
        }

        $only = $this->getOptionAsCollection('only');
        $excludes = $this->getOptionAsCollection('except');

        if ($only->count() > 0) {
            $output['type'] = 'only';
            $output['models'] = $only;
        }

        if ($excludes->count() > 0) {
            $output['type'] = 'except';
            $output['models'] = $excludes;
        }

        if ($only->count() === 0 and $excludes->count() === 0) {
            $requested = $this->requestForFilter();

            switch ($requested['filter']) {
                case 'only':
                    $output['type'] = 'only';
                    $output['models'] = collect($requested['models']);
                    break;
                case 'except':
                    $output['type'] = 'except';
                    $output['models'] = collect($requested['models']);
                    break;
                case 'none':
                default:
            }
        }

        if ($output['models'] !== null) {
            $output['models']->map(function ($model) {
                $this->reduceNamespace($model);
            });
        }

        return $output;
    }

    /**
     * Ask the user if they want to filter their request.
     * If they do, ask them to provide a selection of known models to filter.
     *
     * @return array
     */
    protected function requestForFilter() : array
    {
        $choice = [
            'filter' => '',
            'models' => null,
        ];

        $userChoice = $this->choice('Would you like to filter the search by the model?', [
            'none' => 'No filter',
            'only' => 'Show only certain models',
            'except' => 'Do not show certain models',
        ], 'none');

        switch ($userChoice) {
            case 'only':
                $choice['filter'] = 'only';
                $choice['models'] = $this->choice(
                    'Which models would you like to filter for?',
                    $this->mediaRepository->getDistinctModelTypes()->toArray(),
                    null,
                    null,
                    true
                );
                break;
            case 'except':
                $choice['filter'] = 'except';
                $choice['models'] = $this->choice(
                    'Which models would you like to filter out?',
                    $this->mediaRepository->getDistinctModelTypes()->toArray(),
                    null,
                    null,
                    true
                );
                break;
            case 'none':
            default:
                $choice['filter'] = 'none';
                $choice['models'] = null;
        }

        return $choice;
    }

    /**
     * Get the given argument as a collection.
     *
     * @param string $argument
     * @return Collection
     */
    protected function getOptionAsCollection(string $argument) : Collection
    {
        $provided = $this->option($argument);

        if ($provided === null) {
            return collect();
        }

        return collect(explode(',', $provided))->map(function (string $item) {
            return $this->reduceNamespace($item);
        });
    }

    /**
     * Normalize namespaces as they are stored in the DB.
     *
     * @param string $namespace
     * @return string
     */
    protected function reduceNamespace(string $namespace) : string
    {
        return ltrim($namespace, '\\');
    }
}
