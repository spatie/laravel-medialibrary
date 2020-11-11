<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Collections;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaCollection extends Collection implements Htmlable
{
    public ?string $collectionName = null;

    public ?string $formFieldName = null;

    public function collectionName(string $collectionName): self
    {
        $this->collectionName = $collectionName;

        return $this;
    }

    public function formFieldName(string $formFieldName): self
    {
        $this->formFieldName = $formFieldName;

        return $this;
    }

    public function totalSizeInBytes(): int
    {
        return $this->sum('size');
    }

    public function toHtml()
    {
        return e(json_encode(old($this->formFieldName ?? $this->collectionName) ?? $this->map(function (Media $media) {
            return [
                'name' => $media->name,
                'file_name' => $media->file_name,
                'uuid' => $media->uuid,
                'preview_url' => $media->hasGeneratedConversion('preview') ? $media->getUrl('preview') : '',
                'original_url' => $media->getUrl(),
                'order' => $media->order_column,
                'custom_properties' => $media->custom_properties,
                'extension' => $media->extension,
                'size' => $media->size,
            ];
        })->keyBy('uuid')));
    }

    public function jsonSerialize()
    {
        if (!($this->formFieldName ?? $this->collectionName)) {
            return [];
        }

        return old($this->formFieldName ?? $this->collectionName) ?? $this->map(function (Media $media) {
            return [
                'name' => $media->name,
                'file_name' => $media->file_name,
                'uuid' => $media->uuid,
                'preview_url' => $media->hasGeneratedConversion('preview') ? $media->getUrl('preview') : '',
                'order' => $media->order_column,
                'custom_properties' => $media->custom_properties,
                'extension' => $media->extension,
                'size' => $media->size,
            ];
        })->keyBy('uuid');
    }
}
