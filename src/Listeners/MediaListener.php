<?php

namespace Spatie\MediaLibrary\Listeners;

use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;
use Spatie\MediaLibrary\Media;

class MediaListener
{
    /**
     * Handle entity deleting events.
     * @param $entity
     */
    public function onEntityDeleting($entity)
    {
        if ($entity instanceof HasMediaConversions) {
            $entity->media()->get()->map(function (Media $media) {
                $media->delete();
            });
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'eloquent.deleting*',
            'Spatie\MediaLibrary\Listeners\MediaListener@onEntityDeleting'
        );
    }
}
