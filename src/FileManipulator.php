<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\Facades\File;
use ImagickPixel;
use Spatie\Glide\GlideImage;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Events\ConversionHasBeenCompleted;
use Spatie\MediaLibrary\Helpers\File as MediaLibraryFileHelper;
use Spatie\MediaLibrary\Jobs\PerformConversions;
use Spatie\PdfToImage\Pdf;

class FileManipulator
{
    /**
     * Create all derived files for the given media.
     *
     * @param \Spatie\MediaLibrary\Media $media
     */
    public function createDerivedFiles(Media $media)
    {
        if ($media->type === Media::TYPE_OTHER) {
            return;
        }

        if (in_array($media->type, [Media::TYPE_PDF, Media::TYPE_SVG]) && !class_exists('Imagick')) {
            return;
        }

        $profileCollection = ConversionCollection::createForMedia($media);

        $this->performConversions($profileCollection->getNonQueuedConversions($media->collection_name), $media);

        $queuedConversions = $profileCollection->getQueuedConversions($media->collection_name);

        if (count($queuedConversions)) {
            $this->dispatchQueuedConversions($media, $queuedConversions);
        }
    }

    /**
     * Perform the given conversions for the given media.
     *
     * @param \Spatie\MediaLibrary\Conversion\ConversionCollection $conversions
     * @param \Spatie\MediaLibrary\Media                           $media
     */
    public function performConversions(ConversionCollection $conversions, Media $media)
    {
        $tempDirectory = $this->createTempDirectory();

        $copiedOriginalFile = $tempDirectory.'/'.str_random(16).'.'.$media->extension;

        app(Filesystem::class)->copyFromMediaLibrary($media, $copiedOriginalFile);

        if ($media->type == Media::TYPE_PDF) {
            $copiedOriginalFile = $this->convertPdfToImage($copiedOriginalFile);
        }

        if ($media->type == Media::TYPE_SVG) {
            $copiedOriginalFile = $this->convertSvgToImage($copiedOriginalFile);
        }

        foreach ($conversions as $conversion) {
            $conversionResult = $this->performConversion($media, $conversion, $copiedOriginalFile);

            $renamedFile = MediaLibraryFileHelper::renameInDirectory($conversionResult, $conversion->getName().'.'.
                $conversion->getResultExtension(pathinfo($copiedOriginalFile, PATHINFO_EXTENSION)));

            app(Filesystem::class)->copyToMediaLibrary($renamedFile, $media, true);

            event(new ConversionHasBeenCompleted($media, $conversion));
        }

        File::deleteDirectory($tempDirectory);
    }

    /**
     * Perform the conversion.
     *
     * @param \Spatie\MediaLibrary\Media $media
     * @param Conversion                 $conversion
     * @param string                     $copiedOriginalFile
     *
     * @return string
     */
    public function performConversion(Media $media, Conversion $conversion, string $copiedOriginalFile)
    {
        $conversionTempFile = pathinfo($copiedOriginalFile, PATHINFO_DIRNAME).'/'.string()->random(16).
            $conversion->getName().'.'.$media->extension;

        File::copy($copiedOriginalFile, $conversionTempFile);

        foreach ($conversion->getManipulations() as $manipulation) {
            GlideImage::create($conversionTempFile)
                ->modify($manipulation)
                ->save($conversionTempFile);
        }

        return $conversionTempFile;
    }

    /*
     * Create a directory to store some working files.
     */
    public function createTempDirectory() : string
    {
        $tempDirectory = storage_path('medialibrary/temp/'.str_random(16));

        File::makeDirectory($tempDirectory, 493, true);

        return $tempDirectory;
    }

    protected function convertPdfToImage(string $pdfFile) : string
    {
        $imageFile = string($pdfFile)->pop('.').'.jpg';

        (new Pdf($pdfFile))->saveImage($imageFile);

        return $imageFile;
    }

    protected function convertSvgToImage(string $svgFile) : string
    {
        $imageFile = string($svgFile)->pop('.').'.png';

        $image = new \Imagick();
        $image->readImage($svgFile);
        $image->setBackgroundColor(new ImagickPixel('none'));
        $image->setImageFormat('png32');

        file_put_contents($imageFile, $image);

        return $imageFile;
    }

    /*
     * Dispatch the given conversions.
     */
    protected function dispatchQueuedConversions(Media $media, ConversionCollection $queuedConversions)
    {
        $job = new PerformConversions($queuedConversions, $media);

        $customQueue = config('laravel-medialibrary.queue_name');

        if ($customQueue != '') {
            $job->onQueue($customQueue);
        }

        app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($job);
    }
}
