<?php

namespace Spatie\MediaLibrary\Services;

use Exception;
use Traversable;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaRepository;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Spatie\MediaLibrary\Exceptions\InvalidCheckExistenceFilterType;

class CheckExistence
{
    /** @var  MediaRepository */
    private $repository;

    /** The only supported filter types. */
    const FILTERS = [
        'none',
        'only',
        'except',
    ];

    /**
     * CheckExistence constructor.
     * @param MediaRepository $repository
     */
    public function __construct(MediaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Provide the service of checking for file existence.
     *
     * @param string $filerType
     * @param Collection|null $models
     * @return Traversable
     * @throws Exception
     * @throws InvalidCheckExistenceFilterType
     */
    public function handle(string $filerType = 'none', Collection $models = null) : Traversable
    {
        if (!in_array($filerType, static::FILTERS)) {
            throw new InvalidCheckExistenceFilterType();
        }

        if ($filerType !== 'none' && $models === null) {
            throw new Exception('Models must be provided if the existence filter is not \'none\'');
        }

        switch ($filerType) {
            case 'only':
                $items = $this->getInclusionResults($models);
                break;
            case 'except':
                $items = $this->getExclusionResults($models);
                break;
            case 'none':
            default:
                $items = $this->getAllItems();
        }

        $notFound = collect();

        // First yield the item count for progress indication if needed.
        yield $items->count();

        foreach ($items as $item) {
            // Yield one for every loop item for progress.
            yield 1;
            if (!file_exists($item->getPath())) {
                $notFound->push($item);
            }
        }

        // Use getReturn() in user-space on the generator to get the final output once it is completed.
        return $notFound;
    }

    /**
     * Get the return value right away from handling the task.
     *
     * @param string $filterType
     * @param Collection|null $models
     * @return Collection
     * @throws Exception
     * @throws InvalidCheckExistenceFilterType
     */
    public function handleAndReturn(string $filterType = 'none', Collection $models = null) : Collection
    {
        $generator = $this->handle($filterType, $models);
        foreach ($generator as $item) {
            // Need to loop over the generator to get the return value.
        }
        return $generator->getReturn();
    }

    /**
     * Get all items from the database.
     *
     * @return DbCollection
     */
    protected function getAllItems() : DbCollection
    {
        return $this->repository->all();
    }

    /**
     * Get the exclusive (except) results from the database.
     *
     * @param Collection $models
     * @return DbCollection
     */
    protected function getExclusionResults(Collection $models) : DbCollection
    {
        return $this->repository->getByModelTypesExcept($models->toArray());
    }

    /**
     * Get the inclusive (only) results from the database.
     *
     * @param Collection $models
     * @return DbCollection
     */
    protected function getInclusionResults(Collection $models) : DbCollection
    {
        return $this->repository->getByModelTypes($models->toArray());
    }
}
