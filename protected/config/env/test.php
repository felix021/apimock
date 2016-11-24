<?php

return [
    'components' => [
        'db' => [
            'connectionString' => 'mysql:host=localhost;dbname=apimock',
            'emulatePrepare'   => true,
            'username'         => 'root',
            'password'         => '123456',
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
