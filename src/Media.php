<?php namespace Spatie\MediaLibrary;

use Eloquent;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableInterface;
use Spatie\MediaLibrary\Conversion\ConversionCollectionFactory;
use Spatie\MediaLibrary\Exceptions\UnknownConversionException;
use Spatie\MediaLibrary\Utility\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Media extends Eloquent implements SortableInterface
{
    use Sortable;

    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';
    const TYPE_PDF = 'pdf';

    public $imageProfileUrls = [];

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
        static::deleted(function (Media $media) {
            app(FileSystem::class)->removeFiles($media);
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
     * @return string
     * @throws UnknownConversionException
     */
    public function getUrl($conversionName = '')
    {
        $urlGenerator = app(UrlGeneratorInterface::class)
            ->setMedia($this)
            ->getUrl();

        if ($conversionName != '') {

            $urlGenerator->setConversion(ConversionCollectionFactory::createForMedia($this)->getByName($conversionName));
        }

        return $urlGenerator;
    }

    /**
     * Determine the type of a file.
     *
     * @return string
     */
    public function getType()
    {
        if (in_array($this->getExtension(), ['png', 'jpg', 'jpeg'])) {
            return self::TYPE_IMAGE;
        }

        if ($this->getExtension() == 'pdf') {
            return self::TYPE_PDF;
        }

        return self::TYPE_FILE;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * @return string
     */
    public function getHumanReadableFileSize()
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
