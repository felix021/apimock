<?php

return [
    'components' => [
        'db' => [
            'connectionString' => 'mysql:host=10.10.10.132;dbname=apimock',
            'emulatePrepare'   => true,
            'username'         => 'apimock',
            'password'         => 'apimock',
            'charset'          => 'utf8mb4',
        ],
        'log' => [
            'class' => 'CLogRouter',
            'routes' => [
                [
                    'class'         => 'CFileLogRoute',
                    'levels'        => 'trace, info, error, warning',
                    'maxFileSize'   => 262144, #KB
                ],
            ],
        ],
    ],
];
