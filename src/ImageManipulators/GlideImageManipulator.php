<?php namespace Spatie\MediaLibrary\ImageManipulators;

use Spatie\Glide\GlideImage;
use Spatie\MediaLibrary\Models\Media;
use Queue;
use Spatie\MediaLibrary\QueueHandlers\GlideQueueHandler;

class GlideImageManipulator implements ImageManipulatorInterface
{
    /**
     * Create the derived images for given profiles in a model.
     *
     * @param Media $media
     */
    public function createDerivedFilesForMedia(Media $media)
    {
        $originalFile = $media->getOriginalPath();

        $originalPath = pathinfo($originalFile, PATHINFO_DIRNAME);

        if ($media->getType() == Media::TYPE_IMAGE) {
            $this->createProfileImages($media, $originalFile, $originalPath);
        }
    }

    /**
     * Convert an image using conversionParameters.
     *
     * @param $sourceFile
     * @param $conversionParameters
     * @param $outputFile
     */
    public function convertImage($sourceFile, $conversionParameters, $outputFile)
    {
        $conversionParameters = $this->forceJpgFormat($conversionParameters);

        $glideImage = new GlideImage();

        $glideImage->load($sourceFile, $conversionParameters)
            ->useAbsoluteSourceFilePath()
            ->save($outputFile);
    }

    /**
     * Generate a path and name for the output file.
     *
     * @param Media $media
     * @param $originalPath
     * @param $profileName
     * @param $conversionParameters
     *
     * @return string
     */
    private function determineOutputFileName(Media $media, $originalPath, $profileName, $conversionParameters)
    {
        return $originalPath.'/'.$profileName.'_'.$media->collection_name.'_'.str_replace('=', '_', http_build_query($conversionParameters, null, '_')).'.jpg';
    }

    /**
     * Generates converted images using profiles from model.
     *
     * @param Media $media
     * @param $originalFile
     * @param $originalPath
     */
    private function createProfileImages(Media $media, $originalFile, $originalPath)
    {
        $className = $media->content_type;

        $imageProfiles = $this->getMergedImageProfiles($className);

        foreach ($imageProfiles as $profileName => $conversionParameters) {
            $shouldBeQueued = $this->determineShouldBeQueued($conversionParameters);

            $imageConversionParameters = $this->unsetQueueKey($conversionParameters);

            $outputFile = $this->determineOutputFileName($media, $originalPath, $profileName, $imageConversionParameters);

            if ($shouldBeQueued) {
                Queue::push(
                    GlideQueueHandler::class,
                    [
                        'sourceFile' => $originalFile,
                        'conversionParameters' => $imageConversionParameters,
                        'outputFile' => $outputFile,
                    ],
                    $this->getQueueName()
                );

                return;
            }

            $this->convertImage($originalFile, $imageConversionParameters, $outputFile);
        }
    }

    /**
     * Force the .jpg extension for output files.
     *
     * @param $conversionParameters
     *
     * @return mixed
     */
    private function forceJpgFormat($conversionParameters)
    {
        if (!in_array('fm', $conversionParameters)) {
            $conversionParameters['fm'] = 'jpg';
        }

        return $conversionParameters;
    }

    /**
     * Determine if the job should be queued.
     *
     * @param $conversionParameters
     *
     * @return bool
     */
    private function determineShouldBeQueued($conversionParameters)
    {
        if (array_key_exists('shouldBeQueued', $conversionParameters)) {
            return $conversionParameters['shouldBeQueued'];
        }

        return true;
    }

    /**
     * Delete the Queue-key from the conversionParameters.
     *
     * @param $conversionParameters
     *
     * @return mixed
     */
    private function unsetQueueKey($conversionParameters)
    {
        if (array_key_exists('shouldBeQueued', $conversionParameters)) {
            unset($conversionParameters['shouldBeQueued']);
        }

        return $conversionParameters;
    }

    /**
     * Merge globalImageProfiles and modelImageProfiles, modelImageProfiles override config.
     *
     * @param $className
     *
     * @return array
     */
    private function getMergedImageProfiles($className)
    {
        $modelImageProfiles = $this->getModelImageProfiles($className);

        $globalImageProfiles = config('laravel-medialibrary.globalImageProfiles');

        return array_merge($globalImageProfiles, $modelImageProfiles);
    }

    /**
     * Get the models imageProfiles.
     *
     * @param $className
     *
     * @return array
     */
    private function getModelImageProfiles($className)
    {
        $model = new $className();

        return $model->getImageProfileProperties();
    }

    /**
     * @return string
     */
    private function getQueueName()
    {
        if (config('laravel-medialibrary.queueName') == '')
        {
            return null;
        }

        return config('laravel-medialibrary.queueName');
    }
}
