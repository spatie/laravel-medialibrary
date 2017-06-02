<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDetermined;
use Spatie\MediaLibrary\UrlGenerator\LocalUrlGenerator;
use Route;

class RouteUrlGenerator extends LocalUrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDetermined
     */
    public function getUrl()
    {
        $disk = $this->media->disk;
        $collection = $this->media->collection_name ?? false;

        if ($collection && Route::has($disk.'.show.'.$collection)) {
            return route($disk.'.show.'.$collection, [
                $this->media->model, $this->media
            ]);
        }

        if (Route::has($disk.'.show')) {
            return route($disk.'.show', [
                $this->media->model,
                $this->media
            ]);
        }

        throw new UrlCouldNotBeDetermined('The show media route for "'.$disk.'" has not been set');
    }
}
