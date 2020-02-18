<?php

namespace Spatie\Medialibrary\Models;

use DateTimeInterface;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Spatie\Medialibrary\Conversion\Conversion;
use Spatie\Medialibrary\Conversion\ConversionCollection;
use Spatie\Medialibrary\Filesystem\Filesystem;
use Spatie\Medialibrary\HasMedia\HasMedia;
use Spatie\Medialibrary\Helpers\File;
use Spatie\Medialibrary\Helpers\TemporaryDirectory;
use Spatie\Medialibrary\ImageGenerators\FileTypes\Image;
use Spatie\Medialibrary\Models\Concerns\HasUuid;
use Spatie\Medialibrary\Models\Concerns\IsSorted;
use Spatie\Medialibrary\Models\Traits\CustomMediaProperties;
use Spatie\Medialibrary\ResponsiveImages\RegisteredResponsiveImages;
use Spatie\Medialibrary\UrlGenerator\UrlGeneratorFactory;

class Media extends Model implements Responsable, Htmlable
{
    use IsSorted,
        CustomMediaProperties,
        HasUuid;

    const TYPE_OTHER = 'other';

    protected $guarded = [];

    protected $casts = [
        'manipulations' => 'array',
        'custom_properties' => 'array',
        'responsive_images' => 'array',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFullUrl(string $conversionName = ''): string
    {
        return url($this->getUrl($conversionName));
    }

    public function getUrl(string $conversionName = ''): string
    {
        $urlGenerator = UrlGeneratorFactory::createForMedia($this, $conversionName);

        return $urlGenerator->getUrl();
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, string $conversionName = '', array $options = []): string
    {
        $urlGenerator = UrlGeneratorFactory::createForMedia($this, $conversionName);

        return $urlGenerator->getTemporaryUrl($expiration, $options);
    }

    public function getPath(string $conversionName = ''): string
    {
        $urlGenerator = UrlGeneratorFactory::createForMedia($this, $conversionName);

        return $urlGenerator->getPath();
    }

    public function getImageGenerators(): Collection
    {
        return collect(config('medialibrary.image_generators'));
    }

    public function getTypeAttribute(): string
    {
        $type = $this->getTypeFromExtension();

        if ($type !== self::TYPE_OTHER) {
            return $type;
        }

        return $this->getTypeFromMime();
    }

    public function getTypeFromExtension(): string
    {
        $imageGenerator = $this->getImageGenerators()
            ->map(fn(string $className) => app($className))
            ->first->canHandleExtension(strtolower($this->extension));

        return $imageGenerator
            ? $imageGenerator->getType()
            : static::TYPE_OTHER;
    }

    public function getTypeFromMime(): string
    {
        $imageGenerator = $this->getImageGenerators()
            ->map(fn(string $className) => app($className))
            ->first->canHandleMime($this->mime_type);

        return $imageGenerator
            ? $imageGenerator->getType()
            : static::TYPE_OTHER;
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getHumanReadableSizeAttribute(): string
    {
        return File::getHumanReadableSize($this->size);
    }

    public function getDiskDriverName(): string
    {
        return strtolower(config("filesystems.disks.{$this->disk}.driver"));
    }

    public function getConversionsDiskDriverName(): string
    {
        $diskName = $this->conversions_disk ?? $this->disk;

        return strtolower(config("filesystems.disks.{$diskName}.driver"));
    }

    public function hasCustomProperty(string $propertyName): bool
    {
        return Arr::has($this->custom_properties, $propertyName);
    }

    /**
     * Get the value of custom property with the given name.
     *
     * @param string $propertyName
     * @param mixed $default
     *
     * @return mixed
     */
    public function getCustomProperty(string $propertyName, $default = null)
    {
        return Arr::get($this->custom_properties, $propertyName, $default);
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setCustomProperty(string $name, $value): self
    {
        $customProperties = $this->custom_properties;

        Arr::set($customProperties, $name, $value);

        $this->custom_properties = $customProperties;

        return $this;
    }

    public function forgetCustomProperty(string $name): self
    {
        $customProperties = $this->custom_properties;

        Arr::forget($customProperties, $name);

        $this->custom_properties = $customProperties;

        return $this;
    }

    public function getMediaConversionNames(): array
    {
        $conversions = ConversionCollection::createForMedia($this);

        return $conversions->map(fn(Conversion $conversion) => $conversion->getName())->toArray();
    }

    public function hasGeneratedConversion(string $conversionName): bool
    {
        $generatedConversions = $this->getGeneratedConversions();

        return $generatedConversions[$conversionName] ?? false;
    }

    public function markAsConversionGenerated(string $conversionName, bool $generated): self
    {
        $this->setCustomProperty("generated_conversions.{$conversionName}", $generated);

        $this->save();

        return $this;
    }

    public function getGeneratedConversions(): Collection
    {
        return collect($this->getCustomProperty('generated_conversions', []));
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $downloadHeaders = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type' => $this->mime_type,
            'Content-Length' => $this->size,
            'Content-Disposition' => 'attachment; filename="'.$this->file_name.'"',
            'Pragma' => 'public',
        ];

        return response()->stream(function () {
            $stream = $this->stream();

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $downloadHeaders);
    }

    public function getResponsiveImageUrls(string $conversionName = ''): array
    {
        return $this->responsiveImages($conversionName)->getUrls();
    }

    public function hasResponsiveImages(string $conversionName = ''): bool
    {
        return count($this->getResponsiveImageUrls($conversionName)) > 0;
    }

    public function getSrcset(string $conversionName = ''): string
    {
        return $this->responsiveImages($conversionName)->getSrcset();
    }

    public function toHtml()
    {
        return $this->img();
    }

    /**
     * @param string|array $conversion
     * @param array $extraAttributes
     *
     * @return string
     */
    public function img($conversion = '', array $extraAttributes = []): string
    {
        if (! (new Image())->canHandleMime($this->mime_type)) {
            return '';
        }

        if (is_array($conversion)) {
            $attributes = $conversion;

            $conversion = $attributes['conversion'] ?? '';

            unset($attributes['conversion']);

            $extraAttributes = array_merge($attributes, $extraAttributes);
        }

        $attributeString = collect($extraAttributes)
            ->map(fn($value, $name) => $name.'="'.$value.'"')->implode(' ');

        if (strlen($attributeString)) {
            $attributeString = ' '.$attributeString;
        }

        $loadingAttributeValue = config('medialibrary.default_loading_attribute_value');

        if ($conversion !== '') {
            $conversionObject = ConversionCollection::createForMedia($this)->getByName($conversion);

            $loadingAttributeValue = $conversionObject->getLoadingAttributeValue();
        }

        $media = $this;

        $viewName = 'image';

        $width = '';

        if ($this->hasResponsiveImages($conversion)) {
            $viewName = config('medialibrary.responsive_images.use_tiny_placeholders')
                ? 'responsiveImageWithPlaceholder'
                : 'responsiveImage';

            $width = $this->responsiveImages($conversion)->files->first()->width();
        }

        return view("medialibrary::{$viewName}", compact(
            'media',
            'conversion',
            'attributeString',
            'loadingAttributeValue',
            'width'
        ));
    }

    public function move(HasMedia $model, $collectionName = 'default', string $diskName = ''): self
    {
        $newMedia = $this->copy($model, $collectionName, $diskName);

        $this->delete();

        return $newMedia;
    }

    public function copy(HasMedia $model, $collectionName = 'default', string $diskName = ''): self
    {
        $temporaryDirectory = TemporaryDirectory::create();

        $temporaryFile = $temporaryDirectory->path('/').DIRECTORY_SEPARATOR.$this->file_name;

        app(Filesystem::class)->copyFromMedialibrary($this, $temporaryFile);

        $newMedia = $model
            ->addMedia($temporaryFile)
            ->usingName($this->name)
            ->withCustomProperties($this->custom_properties)
            ->toMediaCollection($collectionName, $diskName);

        $temporaryDirectory->delete();

        return $newMedia;
    }

    public function responsiveImages(string $conversionName = ''): RegisteredResponsiveImages
    {
        return new RegisteredResponsiveImages($this, $conversionName);
    }

    public function stream()
    {
        /** @var \Spatie\Medialibrary\Filesystem\Filesystem $filesystem */
        $filesystem = app(Filesystem::class);

        return $filesystem->getStream($this);
    }

    public function __invoke(...$arguments)
    {
        return new HtmlString($this->img(...$arguments));
    }
}
