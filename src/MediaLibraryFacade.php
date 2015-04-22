<?php namespace Spatie\MediaLibrary;

use Illuminate\Support\Facades\Facade;

class MediaLibraryFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'mediaLibrary';
    }
}
