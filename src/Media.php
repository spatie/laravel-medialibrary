<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Pdf;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Svg;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Image;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Video;
use Spatie\MediaLibrary\UrlGenerator\UrlGeneratorFactory;

class Media extends Model
{
    use SortableTrait;

    const TYPE_OTHER = 'other';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_SVG = 'svg';
    const TYPE_PDF = 'pdf';

    protected $guarded = ['id', 'disk', 'file_name', 'size', 'model_type', 'model_id'];

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
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getUrl(string $conversionName = '') : string
    {
        $urlGenerator = UrlGeneratorFactory::createForMedia($this);

        if ($conversionName !== '') {
            $urlGenerator->setConversion(ConversionCollection::createForMedia($this)->getByName($conversionName));
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
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPath(string $conversionName = '') : string
    {
        $urlGenerator = UrlGeneratorFactory::createForMedia($this);

        if ($conversionName != '') {
            $urlGenerator->setConversion(ConversionCollection::createForMedia($this)->getByName($conversionName));
        }

        return $urlGenerator->getPath();
    }

    /**
     * Collection of all ImageGenerator drivers.
     */
    public function getImageGenerators() : Collection
    {
        return collect([
            Image::class,
            Pdf::class,
            Svg::class,
            Video::class,
        ]);
    }

    /**
     * Determine the type of a file.
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        $type = $this->type_from_extension;
        if ($type !== self::TYPE_OTHER) {
            return $type;
        }

        return $this->type_from_mime;
    }

    /**
     * Determine the type of a file from its file extension.
     *
     * @return string
     */
    public function getTypeFromExtensionAttribute()
    {
        $imageGenerators = $this->getImageGenerators()
            ->map(function (string $className) {
                return app($className);
            });

        foreach ($imageGenerators as $imageGenerator) {
            if ($imageGenerator->canHandleExtension(strtolower($this->extension))) {
                return $imageGenerator->getType();
            }
        }

        return static::TYPE_OTHER;
    }

    /*
     * Determine the type of a file from its mime type
     */
    public function getTypeFromMimeAttribute() : string
    {
        $imageGenerators = $this->getImageGenerators()
            ->map(function (string $className) {
                return app($className);
            });

        foreach ($imageGenerators as $imageGenerator) {
            if ($imageGenerator->canHandleMime($this->getMimeAttribute())) {
                return $imageGenerator->getType();
            }
        }

        return static::TYPE_OTHER;
    }

    public function getMimeAttribute() : string
    {
        return File::getMimetype($this->getPath());
    }

    public function getExtensionAttribute() : string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getHumanReadableSizeAttribute() : string
    {
        return File::getHumanReadableSize($this->size);
    }

    public function getDiskDriverName() : string
    {
        return strtolower(config("filesystems.disks.{$this->disk}.driver"));
    }

    /*
     * Determine if the media item has a custom property with the given name.
     */
    public function hasCustomProperty(string $propertyName) : bool
    {
        return array_key_exists($propertyName, $this->custom_properties);
    }

    /**
     * Determine if the media item has a custom property with the given name
     * using dot notation.
     *
     * @param string $propertyName
     *
     * @return bool
     *
     * @deprecated Will be removed in the next major version in favor of
     * changing `hasCustomProperty` to use dot notation.
     */
    public function hasNestedCustomProperty(string $propertyName) : bool
    {
        return array_has($this->custom_properties, $propertyName);
    }

    /**
     * Get if the value of custom property with the given name.
     *
     * @param string $propertyName
     * @param mixed $default
     *
     * @return mixed
     */
    public function getCustomProperty(string $propertyName, $default = null)
    {
        return $this->custom_properties[$propertyName] ?? $default;
    }

    /**
     * Get a custom property using dot notation.
     *
     * @param string $propertyName
     * @param mixed $default
     *
     * @return mixed
     *
     * @deprecated Will be removed in the next major version in favor of
     * changing `getCustomProperty` to use dot notation.
     */
    public function getNestedCustomProperty(string $propertyName, $default = null)
    {
        return array_get($this->custom_properties, $propertyName, $default);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setCustomProperty(string $name, $value)
    {
        $this->custom_properties = array_merge($this->custom_properties, [$name => $value]);
    }

    /**
     * Set a custom property using dot notation.
     *
     * @param string $name
     * @param mixed $value
     *
     * @deprecated Will be removed in the next major version in favor of
     * changing `setCustomProperty` to use dot notation.
     */
    public function setNestedCustomProperty(string $name, $value)
    {
        // We need to assign `custom_properties` to a variable so we can
        // modify it by reference.
        $customProperties = $this->custom_properties;

        array_set($customProperties, $name, $value);

        $this->custom_properties = $customProperties;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function forgetCustomProperty(string $name)
    {
        if ($this->hasCustomProperty($name)) {
            $customProperties = $this->custom_properties;

            unset($customProperties[$name]);

            $this->custom_properties = $customProperties;
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     *
     * @deprecated Will be renamed to `forgetCustomProperty` in the next
     * major version.
     */
    public function removeCustomProperty(string $name)
    {
        return $this->forgetCustomProperty($name);
    }

    /**
     * Forget a custom property using dot notation.
     *
     * @param string $name
     *
     * @deprecated Will be removed in the next major version in favor of
     * changing `forgetCustomProperty` to use dot notation.
     */
    public function forgetNestedCustomProperty(string $name)
    {
        // We need to assign `custom_properties` to a variable so we can
        // modify it by reference.
        $customProperties = $this->custom_properties;

        array_forget($customProperties, $name);

        $this->custom_properties = $customProperties;
    }

    /*
     * Get all the names of the registered media conversions.
     */
    public function getMediaConversionNames(): array
    {
        $conversions = ConversionCollection::createForMedia($this);

        return $conversions->map(function (Conversion $conversion) {
            return $conversion->getName();
        })->toArray();
    }
}
