<?php

namespace Programic\MediaLibrary;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Programic\MediaLibrary\MediaCollections\Models\Media;

interface HasAttachableMedia
{
    public function attachableMedia(): MorphToMany;

    /**
     * @param array|Collection|Media $ids
     * @param bool $detaching Detach media missing in $ids?
     * @return array
     */
    public function attachMedia(array|Media|Collection $ids, bool $detaching): array;

    /**
     * @param array|Collection|Media $ids
     * @return int
     */
    public function detachMedia(array|Media|Collection $ids): int;

//    public function hasAttachableMedia(string $collectionName = ''): bool;
//
//    public function getAttachableMedia(string $collectionName = 'default', array|callable $filters = []): Collection;
}
