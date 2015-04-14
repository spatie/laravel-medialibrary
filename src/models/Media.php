<?php namespace Spatie\MediaLibrary\Models;

use Eloquent;

class Media extends Eloquent {

    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';
    const TYPE_PDF = 'pdf';

    protected $table = 'media';

    public $imageProfileURLs = [];

    public function content()
    {
        return $this->morphTo();
    }

    public function getOriginalPath()
    {
        return config('laravel-medialibrary.publicPath') . '/' . $this->id . '/' . $this->path;
    }

    public function getOriginalURL()
    {
        return '/' . $this->id . '/' . $this->path;
    }

    public static function getHighestNumberOrder()
    {
        return ((int) self::max('order_column')) + 1;
    }

    public function getType()
    {
        switch($this->extension)
        {
            case 'png';
            case 'jpg':
            case 'jpeg':
                $type = self::TYPE_IMAGE;
                break;
            case 'pdf':
                $type = self::TYPE_PDF;
                break;
            case 'file':
                $type = self::TYPE_FILE;
        }

        return $type;
    }

    public function addImageProfileURL($profileName, $path)
    {

        $this->imageProfileURLs[$profileName] = $path;

        return $this;
    }

    public function getAllProfileURLs()
    {
        return $this->imageProfileURLs;
    }

    public function getURL($profile)
    {
        if(is_array($profile))
        {
            return $this->createGlideImageURL($profile);
        }

        return array_key_exists($profile, $this->imageProfileURLs) ? $this->imageProfileURLs[$profile] : false;
    }

    public function createGlideImageURL($profile)
    {
        return GLideImage::setImagePath($this->getOriginalURL())
            ->setConversionParameters($profile)
            ->getURL();
    }
}