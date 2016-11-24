<?php

return [
    'components' => [
        'db' => [
            'connectionString' => 'mysql:host=localhost;dbname=apimock',
            'emulatePrepare'   => true,
            'username'         => 'test',
            'password'         => 'test',
            'charset'          => 'utf8mb4',
        ],
        'log' => [
            'class' => 'CLogRouter',
            'routes' => [
                [
                    'class'         => 'CFileLogRoute',
                    'levels'        => 'info, error, warning',
                    'maxFileSize'   => 1048576, #KB
                    'maxLogFiles'   => 10,
                ],
            ],
        ],
    ],
];
