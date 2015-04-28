<?php

return [

    /*
     * The medialibrary will use this directory to store added files and derived images.
     * If you are planning on using the url to the derived images, make sure
     * you specify a directory inside Laravel's public path.
     *
     * The package will automatically add a .gitignore file to this directory
     * so you don't end of committing these files in your repo.
     */
    'publicPath' => public_path().'/media',

    /*
     * The maximum file size of an item in bytes. If you try to add a file
     * that is larger to the medialibrary it will result in an exception.
     */
    'maxFileSize' => 1024 * 1024 * 10,

    /*
     * These image profiles will applied on all used that implement
     * the MediaLibraryModelTrait.
     *
     * See the README of the package for an example.
     */
    'globalImageProfiles' => [],

    /*
    * The medialibrary will used this queue to generate derived images.
    * Leave empty to use the default queue.
    */
    'queueName' => 'media_queue',
];
