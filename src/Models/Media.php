<?php namespace Spatie\MediaLibrary\Models;

use Eloquent;
use GlideImage;

class Media extends Eloquent
{
    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';
    const TYPE_PDF = 'pdf';

    protected $table = 'media';

    public $imageProfileURLs = [];

    /**
     * Create the polymorphic relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function content()
    {
        return $this->morphTo();
    }

    /**
     * Get the original path for a media-file.
     *
     * @return string
     */
    public function getOriginalPath()
    {
        return config('laravel-medialibrary.publicPath').'/'.$this->id.'/'.$this->path;
    }

    /**
     * Get the original URL to a media-file.
     *
     * @return string
     */
    public function getOriginalURL()
    {
        return '/'.$this->id.'/'.$this->path;
    }

    /**
     * Get the next integer for sorting.
     *
     * @return int
     */
    public static function getHighestNumberOrder()
    {
        return ((int) self::max('order_column')) + 1;
    }

    /**
     * Determine the type of a file.
     *
     * @return string
     */
    public function getType()
    {
        switch ($this->extension) {
            case 'png';
            case 'jpg':
            case 'jpeg':
                $type = self::TYPE_IMAGE;
                break;
            case 'pdf':
                $type = self::TYPE_PDF;
                break;
            default:
                $type = self::TYPE_FILE;
                break;
        }

        return $type;
    }

    /**
     * Generate a URL to the image-profile.
     *
     * @param $profileName
     * @param $path
     *
     * @return $this
     */
    public function addImageProfileURL($profileName, $path)
    {
        $this->imageProfileURLs[$profileName] = $path;

        return $this;
    }

    /**
     * Get all URL's for an image-profile.
     *
     * @return array
     */
    public function getAllProfileURLs()
    {
        return $this->imageProfileURLs;
    }

    /**
     * Get the URL to a generated Glide-image.
     *
     * @param $profile
     *
     * @return bool
     */
    public function getURL($profile)
    {
        if (is_array($profile)) {
            return $this->createGlideImageURL($profile);
        }

        return array_key_exists($profile, $this->imageProfileURLs) ? $this->imageProfileURLs[$profile] : false;
    }

    /**
     * Create a URL for a generated Glide-image.
     *
     * @param $profile
     *
     * @return mixed
     */
    public function createGlideImageURL($profile)
    {
        return GlideImage::setImagePath($this->getOriginalURL())
            ->setConversionParameters($profile)
            ->getURL();
    }
}
