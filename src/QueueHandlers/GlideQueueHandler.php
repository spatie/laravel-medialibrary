<?php namespace Spatie\MediaLibrary\QueueHandlers;

use Spatie\MediaLibrary\ImageManipulators\ImageManipulatorInterface;

class GlideQueueHandler
{
    protected $imageManipulator;

    public function __construct(ImageManipulatorInterface $imageManipulator)
    {
        $this->imageManipulator = $imageManipulator;
    }

    /**
     * Fire the queue job.
     *
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->imageManipulator->convertImage(
            $data['sourceFile'],
            $data['conversionParameters'],
            $data['outputFile']
        );

        $job->delete();
    }
}
