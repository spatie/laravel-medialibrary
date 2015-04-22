<?php

return [

    /*
     * publicPath is the path where the saved media-items will be stored.
     */
    'publicPath' => public_path().'/medialibrary',

    /*
     * Maximum allowed filesize
     */
    'maxFileSize' => 1024 * 1024 * 10,

    /*
     * Standard image profiles applied on all used models.
     * If a model contains a profile with same key as one defined here,
     *  the models profile will overwrite this one
     *
     * See Readme for an example
     */
    'globalImageProfiles' => [],
];
