<?php namespace Spatie\MediaLibrary\Helpers;

use Intervention\Image\ImageManager;
use League\Glide\Api\Api;
use League\Glide\Api\Manipulator\Blur;
use League\Glide\Api\Manipulator\Brightness;
use League\Glide\Api\Manipulator\Contrast;
use League\Glide\Api\Manipulator\Filter;
use League\Glide\Api\Manipulator\Gamma;
use League\Glide\Api\Manipulator\Orientation;
use League\Glide\Api\Manipulator\Output;
use League\Glide\Api\Manipulator\Pixelate;
use League\Glide\Api\Manipulator\Rectangle;
use League\Glide\Api\Manipulator\Sharpen;
use League\Glide\Api\Manipulator\Size;

use Spatie\MediaLibrary\Interfaces\ImageManipulatorInterface;
use Spatie\MediaLibrary\Models\Media;
use Queue;
use Symfony\Component\HttpFoundation\Request;

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
     * Convert an image using conversionParameters
     *
     * @param $sourceFile
     * @param $conversionParameters
     * @param $outputFile
     */
    public function convertImage($sourceFile, $conversionParameters, $outputFile)
    {
        $conversionParameters = $this->forceJpgFormat($conversionParameters);

        $this->renderImage($this->prepareGlideApi(), $conversionParameters, $sourceFile, $outputFile);
    }

    /**
     * Generate a path and name for the output file
     *
     * @param Media $media
     * @param $originalPath
     * @param $profileName
     * @param $conversionParameters
     * @return string
     */
    private function determineOutputFileName(Media $media, $originalPath, $profileName, $conversionParameters)
    {
        return $originalPath . '/' . $profileName . '_' . $media->collection_name . '_' . str_replace('=', '_', http_build_query($conversionParameters, null, '_')) . '.jpg';
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

        foreach ($className::getImageProfileProperties() as $profileName => $conversionParameters) {

            $shouldBeQueued = $this->determineShouldBeQueued($conversionParameters);

            $conversionParameters = $this->unsetQueueKey($conversionParameters);

            if( ! $shouldBeQueued)
            {
                $outputFile = $this->determineOutputFileName($media, $originalPath, $profileName, $conversionParameters);

                $this->convertImage($originalFile, $conversionParameters, $outputFile);

            } else {

                Queue::push(
                    GlideQueueHandler::class,
                    [
                        'sourceFile' => $originalFile,
                        'conversionParameters' => $conversionParameters,
                        'outputFile' => $this->determineOutputFileName($media, $originalPath, $profileName, $conversionParameters),
                    ],
                    'media_queue'
                );
            }
        }
    }

    /**
     * Create the Image-manipulation API with Manipulators
     *
     * @return Api
     */
    private function prepareGlideApi()
    {
        $manipulators = $this->setGlideManipulators();

        $api = new Api(new ImageManager(), $manipulators);

        return $api;
    }

    /**
     * Force the .jpg extension for output files
     *
     * @param $conversionParameters
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
     * Insantiate all Glide-manipulators
     *
     * @return array
     */
    private function setGlideManipulators()
    {
        return [
            new Orientation(),
            new Rectangle(),
            new Size(2000 * 2000),
            new Brightness(),
            new Contrast(),
            new Gamma(),
            new Sharpen(),
            new Filter(),
            new Blur(),
            new Pixelate(),
            new Output(),
        ];
    }

    /**
     * Render the image using the API
     *
     * @param Api $api
     * @param $conversionParameters
     * @param $sourceFile
     * @param $outputFile
     */
    private function renderImage(Api $api, $conversionParameters, $sourceFile, $outputFile)
    {
        $imageData = $api->run(Request::create(null, null, $conversionParameters), file_get_contents($sourceFile));

        file_put_contents($outputFile, $imageData);
    }

    /**
     * Determine if the job should be queued
     *
     * @param $conversionParameters
     * @return bool
     */
    private function determineShouldBeQueued($conversionParameters)
    {
        if(array_key_exists('shouldBeQueued', $conversionParameters))
        {
            return $conversionParameters['shouldBeQueued'];
        }

        return true;
    }

    /**
     * Delete the Queue-key from the conversionParameters
     *
     * @param $conversionParameters
     * @return mixed
     */
    private function unsetQueueKey($conversionParameters)
    {
        if(array_key_exists('shouldBeQueued', $conversionParameters))
        {
            unset($conversionParameters['shouldBeQueued']);
        }

        return $conversionParameters;
    }
}
