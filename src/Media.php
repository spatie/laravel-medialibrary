<?php

namespace Spatie\MediaLibrary;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Conversion\ConversionCollectionFactory;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\UrlGenerator\UrlGeneratorFactory;

class Media extends Model
{
    use SortableTrait;

    const TYPE_OTHER = 'other';
    const TYPE_IMAGE = 'image';
    const TYPE_PDF = 'pdf';

    public $imageProfileUrls = [];

    public $previousManipulations = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'manipulations' => 'array',
        'custom_properties' => 'array',
    ];

    /**
     * Create the polymorphic relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Get the original Url to a media-file.
     *
     * @param string $conversionName
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UnknownConversion
     */
    public function getUrl($conversionName = '')
    {
        $urlGenerator = UrlGeneratorFactory::createForMedia($this);

        if ($conversionName != '') {
            $urlGenerator->setConversion(ConversionCollectionFactory::createForMedia($this)->getByName($conversionName));
        }

        return $urlGenerator->getUrl();
    }

    /**
     * Get the original path to a media-file.
     *
     * @param string $conversionName
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UnknownConversion
     */
    public function getPath($conversionName = '')
    {
        $urlGenerator = UrlGeneratorFactory::createForMedia($this);

        if ($conversionName != '') {
            $urlGenerator->setConversion(ConversionCollectionFactory::createForMedia($this)->getByName($conversionName));
        }

        return $urlGenerator->getPath();
    }

    /**
     * Determine the type of a file.
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        $extension = strtolower($this->extension);

        if (in_array($extension, ['png', 'jpg', 'jpeg'])) {
            return static::TYPE_IMAGE;
        }

        if ($extension == 'pdf') {
            return static::TYPE_PDF;
        }

        return static::TYPE_OTHER;
    }

    /**
     * @return string
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * @return string
     */
    public function getHumanReadableSizeAttribute()
    {
        return File::getHumanReadableSize($this->size);
    }

    public function getDiskDriverName()
    {
        return config('filesystems.disks.'.$this->disk.'.driver');
    }
}
