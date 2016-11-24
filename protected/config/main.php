<?php

if (!isset($_SERVER['SITE_ENV'])) {
    $_SERVER['SITE_ENV'] = 'homestead';
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

    'defaultController' => 'apieditor',

    // application components
    'components' => [
        'user' => [
            // enable cookie-based authentication
            'allowAutoLogin' => true,
        ],
        'urlManager' => [
            'showScriptName'  =>  false,
            'urlFormat' => 'path',
            'caseSensitive'  => false,
            'rules' => [
                'gii'       => 'gii',
                'gii/<action:.*>'       => 'gii/<action>',
                'test/test' => 'test/test',
                'apieditor' => 'apieditor/index',
                'apieditor/<action:\w*>' => 'apieditor/<action>',
                '(\w+/?)+' => 'api/fetch',
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
