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

    protected $guarded = ['id', 'disk', 'file_name', 'size', 'model_type', 'model_id'];

    public $imageProfileUrls = [];

    public $hasModifiedManipulations = false;

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
        $type = $this->type_from_extension;
        if ($type !== Media::TYPE_OTHER) {
            return $type;
        }

        return $this->type_from_mime;
    }

    /**
     * Determine the type of a file from its file extension
     *
     * @return string
     */
    public function getTypeFromExtensionAttribute()
    {
        $extension = strtolower($this->extension);

        if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) {
            return static::TYPE_IMAGE;
        }

        if ($extension == 'pdf') {
            return static::TYPE_PDF;
        }

        return static::TYPE_OTHER;
    }

    /**
     * Determine the type of a file from its mime type
     *
     * @return string
     */
    public function getTypeFromMimeAttribute()
    {
        if ($this->getDiskDriverName() != 'local') {
            return static::TYPE_OTHER;
        }

        $mime = File::getMimetype($this->getPath());

        if (in_array($mime, ['image/jpeg', 'image/gif', 'image/png'])) {
            return static::TYPE_IMAGE;
        }

        if ($mime == 'application/pdf') {
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

    /**
     * @return string
     */
    public function getDiskDriverName()
    {
        return config('filesystems.disks.'.$this->disk.'.driver');
    }

    /**
     * Determine if the media item has a custom property with the given name.
     *
     * @param string $propertyName
     *
     * @return bool
     */
    public function hasCustomProperty($propertyName)
    {
        return array_key_exists($propertyName, $this->custom_properties);
    }

    /**
     * Get if the value of custom property with the given name.
     *
     * @param string $propertyName
     * @param mixed  $propertyName
     *
     * @return mixed
     */
    public function getCustomProperty($propertyName, $default = null)
    {
        if (!$this->hasCustomProperty($propertyName)) {
            return $default;
        }

        return $this->custom_properties[$propertyName];
    }

    public function setCustomProperty($name, $value)
    {
        $this->custom_properties = array_merge($this->custom_properties, [$name => $value]);
    }
}
