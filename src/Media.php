<?php namespace Spatie\MediaLibrary;

use Eloquent;
use GlideImage;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableInterface;
use Spatie\MediaLibrary\Utility\File;

class Media extends Eloquent implements SortableInterface
{
    use Sortable;

    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';
    const TYPE_PDF = 'pdf';

    protected $table = 'media';

    public $imageProfileUrls = [];

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
     * Get the original Url to a media-file.
     *
     * @return string
     */
    public function getOriginalUrl()
    {
        return substr($this->getOriginalPath(), strlen(public_path()));
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
     * Generate a Url to the image-profile.
     *
     * @param $profileName
     * @param $path
     *
     * @return $this
     */
    public function addImageProfileUrl($profileName, $path)
    {
        $this->imageProfileUrls[$profileName] = $path;

        return $this;
    }

    /**
     * Get all Url's for an image-profile.
     *
     * @return array
     */
    public function getAllProfileUrls()
    {
        return $this->imageProfileUrls;
    }

    /**
     * Get the Url to the original file or the generated Glide-image.
     *
     * @param $profile|null
     *
     * @return bool
     */
    public function getUrl($profile = null)
    {
        if (! $profile) {
            return $this->getOriginalUrl();
        }
        
        if (is_array($profile)) {
            return $this->createGlideImageUrl($profile);
        }

        return array_key_exists($profile, $this->imageProfileUrls) ? $this->imageProfileUrls[$profile] : false;
    }

    /**
     * Get the path to the file of the given profile
     *
     * @param $profile
     * @return bool|string
     */
    public function getPath($profile)
    {
        $paths = app()->make('Spatie\MediaLibrary\FileSystems\FileSystemInterface')->getFilePathsForMedia($this);

        return array_key_exists($profile, $paths) ? $this->$paths[$profile] : false;
    }

    /**
     * Create a Url for a generated Glide-image.
     *
     * @param $profile
     *
     * @return mixed
     */
    public function createGlideImageUrl($profile)
    {
        return GlideImage::setImagePath($this->getOriginalUrl())
            ->setConversionParameters($profile)
            ->getUrl();
    }

    public function getHumanReadableFileSize()
    {
        return File::getHumanReadableFileSize($this->size);
    }
}
