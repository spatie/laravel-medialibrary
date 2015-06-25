<?php

return [

    /*
     * The filesystems you on which to store added files and derived images. Choose one or more
     * of the filesystems you configured in app/config/filesystems.php
     */
    'filesystem' => 'media',

    /*
     * The medialibrary will use this directory to store added files and derived images.
     * This path is relatid to the root you configured on the chosen filesystem.
     *
     */
    'storage_path' => '',

    /*
     * The maximum file size of an item in bytes. Adding a file
     * that is larger will result in an exception.
     */
    'max_file_size' => 1024 * 1024 * 10,

    /*
     * These image profiles will applied on all used that implement
     * the MediaLibraryModelTrait.
     *
     * See the README of the package for an example.
     */
    'global_image_profiles' => [],

    /*
    * The medialibrary will used this queue to generate derived images.
    * Leave empty to use the default queue.
    */
    'queue_name' => 'media_queue',
];
