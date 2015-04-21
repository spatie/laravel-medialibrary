<?php

return [
    'publicPath' => public_path() . '/content/media',
    'maxFileSize' => 1024 * 1024 * 10,
    'globalImageProfiles' => [
        'small' => ['w' => '300', 'h' => '300', 'filt' => 'greyscale'],
        ],
];
