<?php

namespace Spatie\MediaLibrary\Profile;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Media;

class ProfileCollection extends Collection
{
    /**
     * @var \Spatie\MediaLibrary\Profile\Media
     */
    protected $media;

    /**
     * @param Media $media
     * @return $this
     */
    public function setMedia(Media $media)
    {
        $this->media = $media;

        $this->items = [];

        foreach ($this->media->model->getMediaProfileProperties() as $profileArray)
        {

            $this->items[] = new Profile($profileArray);
        }

        foreach($media->profile_properties as $profileName => $conversion)
        {
            $this->getProfile($profileName)->addAsFirstConversion($media->profile_properties);
        }

        return $this;
    }

    public function getProfilesForCollection($collectionName)
    {
        $collectionProfileNames = $this->media->content()->getMediaProfileNames($collectionName);

        return $this->filter(function(Profile $profile) use ($collectionProfileNames) {
            return in_array($profile->getName(), $collectionProfileNames);
        });
    }

    public function getProfile($name)
    {
        foreach ($this->items as $profile) {
            if ($profile->getName() == $name) {
                return $profile;
            }
        }

        return null;
    }


}