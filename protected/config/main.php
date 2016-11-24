<?php

if (!isset($_SERVER['SITE_ENV'])) {
    $_SERVER['SITE_ENV'] = 'local';
}

$env_config = require(__DIR__ . '/env/' . $_SERVER['SITE_ENV'] . '.php');

$main_config = [
    'basePath' => dirname(__DIR__),
    'name' => 'apimock',

    'preload' => ['log'],

    'import' => [
        'application.models.*',
        'application.components.*',
    ],

    'modules' => [
    ],

    // application components
    'components' => [
        'user' => [
            // enable cookie-based authentication
            'allowAutoLogin' => true,
        ],
        'urlManager' => [
            'showScriptName'  =>  false,
            'urlFormat' => 'path',
            'rules' => [
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
            ],
        ],
        'errorHandler' => [
            'class'  =>  'Err',
        ],
    ],

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => [
        // this is used in contact page
        'adminEmail' => 'webmaster@example.com',
    ],
];

return CMap::mergeArray($main_config, $env_config);
