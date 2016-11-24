<?php

// change the following paths if necessary
$yii = __DIR__ . '/../framework/yii.php';
$config = __DIR__ . '/protected/config/main.php';

if ($_SERVER['SITE_ENV'] != 'production') {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
}

// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

require_once($yii);
Yii::createWebApplication($config)->run();
