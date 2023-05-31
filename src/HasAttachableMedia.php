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
    public function attachMedia(array|Media|Collection $ids, $detaching): array;

//    public function hasAttachableMedia(string $collectionName = ''): bool;
//
//    public function getAttachableMedia(string $collectionName = 'default', array|callable $filters = []): Collection;
}
