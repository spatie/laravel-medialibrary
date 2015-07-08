<?php

namespace Spatie\MediaLibrary;

use Eloquent;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableInterface;
use Spatie\MediaLibrary\Conversion\ConversionCollectionFactory;
use Spatie\MediaLibrary\Exceptions\UnknownConversion;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Media extends Eloquent implements SortableInterface
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
     * @throws UnknownConversion
     */
    public function getUrl($conversionName = '')
    {
        $urlGenerator = app(UrlGeneratorInterface::class)->setMedia($this);

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
    public function getType()
    {
        if (in_array($this->extension, ['png', 'jpg', 'jpeg'])) {
            return self::TYPE_IMAGE;
        }

        if ($this->extension == 'pdf') {
            return self::TYPE_PDF;
        }

        return self::TYPE_OTHER;
    }

    /**
     * @return string
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get the size of the file in bytes.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getHumanReadableSize()
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        if ($this->size == 0) {
            return '0 '.$units[1];
        }

        for ($i = 0; $this->size > 1024; $i++) {
            $this->size /= 1024;
        }

        return round($this->size, 2).' '.$units[$i];
    }
}
