<?php namespace Spatie\MediaLibrary\Helpers;


class GlideQueueHandler {

    /**
     * Fire the queue job
     *
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $imageManipulator = new GlideImageManipulator();

        $imageManipulator->convertImage(
            $data['sourceFile'],
            $data['conversionParameters'],
            $data['outputFile']
        );

        $job->delete();
    }
}
