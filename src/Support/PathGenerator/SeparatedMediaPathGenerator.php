<?php

/**
 * Custom path generator which extends DefaultPathGenerator and makes media separated by folders, subfolders and model name. 
 * If yo want make your media separated use this generator class in /config/media-library.php (path_generator). 
 * It makes custom directory stucture and stores images in subfolders named as model name (example: page, post, product, ...) 
 * /media/post/{post_id}/{media_id}/image.jpg
 * 
 * This way reduces amount of folders inside media storage folder, makes structure more readable, simple and clear. 
 * In case you have a lot of different media for pages, posts, products etc... this generator can help to organize media in structured tree
 * 
 * Example: The structure for post (post id 10) is going to be:
 * /media/post/10/87/image87.jpg
 * /media/post/10/87/conversions/thumb87_1.jpg
 * /media/post/10/87/conversions/thumb87_2.jpg
 * /media/post/10/87/conversions/thumb87_3.jpg
 * /media/post/10/87/responsive-images/img87_1.jpg
 * /media/post/10/87/responsive-images/img87_2.jpg
 * 
 * /media/post/10/88/image88.jpg
 * /media/post/10/89/image89.jpg
 * /media/post/10/89/image90.jpg
 * 
 * The structure: 
 * - /media/post
 *      - /10
 *          - /87
 *              - image87.jpg
 *              - /conversions
 *                  - thumb87_1.jpg
 *                  - thumb87_2.jpg
 *                  - thumb87_3.jpg
 *              - /responsive-images
 *                  - img87_1.jpg
 *                  - img87_2.jpg
 * 
 * As you see, images are grouped and separated
 * 
 */

namespace Spatie\MediaLibrary\Support\PathGenerator;

use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SeparatedMediaPathGenerator extends DefaultPathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    /**
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $modelType = explode("\\", $media->model_type);
        $modelType = strtolower(end($modelType));

        return $modelType . '/' . $media->model->id . '/' . $media->getKey();
    }
}
