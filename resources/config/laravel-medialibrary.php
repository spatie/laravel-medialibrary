<?php

return [

    /*
     * The filesystems on which to store added files and derived images by default. Choose
     * one or more of the filesystems you configured in app/config/filesystems.php
     */
    'defaultFilesystem' => 'media',

    /*
     * The maximum file size of an item in bytes. Adding a file
     * that is larger will result in an exception.
     */
    'max_file_size' => 1024 * 1024 * 10,

    /*
     * This queue will used to generate derived images.
     * Leave empty to use the default queue.
     */
    'queue_name' => '',

    /*
     * The class name of the media model to be used.
     */
    'media_model' => Spatie\MediaLibrary\Media::class,

    /*
     * When urls to files get generated this class will be called. Leave empty
     * if your files are stored locally above the site root or on s3.
     */
    'custom_url_generator_class' => null,

    /*
     * The class that contains the strategy for determining a media file's path.
     */
    'custom_path_generator_class' => null,

    's3' => [
        /*
         * The domain that should be prepended when generating urls.
         */
        'domain' => 'https://xxxxxxx.s3.amazonaws.com',
    ],

    'remote' => [
        /*
         * Any extra headers that should be included when uploading media to
         * a remote disk. Even though supported headers may vary between
         * different drivers, a sensible default has been provided.
         *
         * Supported by S3: CacheControl, Expires, StorageClass,
         * ServerSideEncryption, Metadata, ACL, ContentEncoding
         */
        'extra_headers' => [
            'CacheControl' => 'max-age=604800',
        ]
    ]
];
