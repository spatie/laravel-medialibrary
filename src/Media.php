<?php

namespace Spatie\MediaLibrary;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableInterface;
use Spatie\MediaLibrary\Conversion\ConversionCollectionFactory;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\UrlGenerator\UrlGenerator;

class Media extends Model implements SortableInterface
{
    use Sortable;

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
    ];

    public static function boot()
    {
        static::updating(function (Media $media) {
            $media->previousManipulations = $media->getOriginal('manipulations');
        });

        static::updated(function (Media $media) {
            if ($media->manipulations != $media->previousManipulations) {
                app(FileManipulator::class)->createDerivedFiles($media);
            }
        });

        static::deleted(function (Media $media) {
            app(Filesystem::class)->removeFiles($media);
        });

        parent::boot();
    }

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
        $urlGenerator = app(UrlGenerator::class)->setMedia($this);

        if ($conversionName != '') {
            $urlGenerator->setConversion(ConversionCollectionFactory::createForMedia($this)->getByName($conversionName));
        }

        return $urlGenerator->getUrl();
    }

    /**
     * Determine the type of a file.
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        if (in_array($this->extension, ['png', 'jpg', 'jpeg'])) {
            return static::TYPE_IMAGE;
        }

        if ($this->extension == 'pdf') {
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
}
