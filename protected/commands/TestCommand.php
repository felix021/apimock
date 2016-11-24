<?php

class TestCommand extends CConsoleCommand
{
    public function actionTest()
    {
        printf("This is %s@%s\n", Yii::app()->name, gethostname());
    }

    public function actionMySQL($db = 'db')
    {
        $db = Yii::app()->$db;
        $arr_kv = explode(';', substr($db->connectionString, 6));
        $conf = [];
        foreach ($arr_kv as $kv) {
            list($key, $val) = explode('=', $kv);
            $conf[$key] = $val;
        }
        printf(
            "mysql -h%s -u%s -p%s %s\n",
            escapeshellarg(@$conf['host'] ?: '127.0.0.1'),
            escapeshellarg($db->username),
            escapeshellarg($db->password),
            escapeshellarg(@$conf['dbname'] ?: '')
        );
    }

    public function actionRebuild()
    {
        if ($_SERVER['SITE_ENV'] == 'production') {
            die("be careful!");
        }
        $db_name = Yii::app()->name;
        Yii::app()->db->createCommand("drop database if exists $db_name")->execute();
        Yii::app()->db->createCommand(file_get_contents(Yii::app()->basePath . "/data/$db_name.sql"))->execute();
    }
}
