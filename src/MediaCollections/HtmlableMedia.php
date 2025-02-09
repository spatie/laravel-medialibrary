<?php

namespace Spatie\MediaLibrary\MediaCollections;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Image;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGeneratorFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HtmlableMedia implements \Stringable, Htmlable
{
    protected string $conversionName = '';

    protected array $extraAttributes = [];

    protected string $loadingAttributeValue = '';

    public function __construct(
        protected Media $media
    ) {}

    /**
     * @return $this
     */
    public function attributes(array $attributes): self
    {
        if (is_array($attributes['class'] ?? null)) {
            $attributes['class'] = Arr::toCssClasses($attributes['class']);
        }

        if (is_array($attributes['style'] ?? null)) {
            $attributes['style'] = Arr::toCssStyles($attributes['style']);
        }

        $this->extraAttributes = $attributes;

        return $this;
    }

    /**
     * @return $this
     */
    public function conversion(string $conversionName): self
    {
        $this->conversionName = $conversionName;

        return $this;
    }

    /**
     * @return $this
     */
    public function lazy(): self
    {
        $this->loadingAttributeValue = ('lazy');

        return $this;
    }

    public function toHtml(): string
    {
        $imageGenerator = ImageGeneratorFactory::forMedia($this->media) ?? new Image;

        if (! $imageGenerator->canHandleMime($this->media->mime_type)) {
            return '';
        }

        $attributeString = collect($this->extraAttributes)
            ->map(fn ($value, $name) => $name.'="'.$value.'"')->implode(' ');

        if (strlen($attributeString)) {
            $attributeString = ' '.$attributeString;
        }

        $loadingAttributeValue = config('media-library.default_loading_attribute_value');

        if ($this->conversionName !== '') {
            $conversionObject = ConversionCollection::createForMedia($this->media)->getByName($this->conversionName);

            $loadingAttributeValue = $conversionObject->getLoadingAttributeValue();
        }

        if ($this->loadingAttributeValue !== '') {
            $loadingAttributeValue = $this->loadingAttributeValue;
        }

        $viewName = 'image';
        $width = '';
        $height = '';

        if ($this->media->hasResponsiveImages($this->conversionName)) {
            $viewName = config('media-library.responsive_images.use_tiny_placeholders')
                ? 'responsiveImageWithPlaceholder'
                : 'responsiveImage';

            $responsiveImage = $this->media->responsiveImages($this->conversionName)->files->first();

            $width = $responsiveImage->width();
            $height = $responsiveImage->height();
        }

        $media = $this->media;
        $conversion = $this->conversionName;

        return view("media-library::{$viewName}", compact(
            'media',
            'conversion',
            'attributeString',
            'loadingAttributeValue',
            'width',
            'height',
        ))->render();
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }
}
