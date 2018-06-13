<?php

return [
    'root_dir' => 'files', //root directory 
    'root_dir_tmp' => 'files/tmp', //temporary directory name
    'accept_ext' => 'jpg|jpeg|png|gif|doc|docx|xls|xlsx|pdf|zip|rar|7z',
    'accept_dir' => [
        'files',
    ],
    'scale' => [
    ],
    'max_size' => 10 * (1024 * 1024), // byte
    'thumb' => array(
        'width' => 150,
        'height' => 150
    ),
    'quality' => intval(env('FILEUPLOAD_QUALITY', 80)),
    'default_storage' => env('FILEUPLOAD_STORAGE', 'local'),
    'storage' => [
        'local' => [            
        ],
        'softlayer' => [
            'url_auth' => env('SOFTLAYER_URL_AUTH', ''),
            'url_cdn' => env('SOFTLAYER_URL_CND', ''),
            'url' => env('SOFTLAYER_URL', ''),
            'user' => env('SOFTLAYER_USER', ''),
            'key' => env('SOFTLAYER_KEY', ''),
            'container' => env('SOFTLAYER_CONTAINER', ''),
        ],
        'azure' => [
            
        ],
    ],
];
