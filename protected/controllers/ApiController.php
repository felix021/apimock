<?php

class ApiController extends CController
{
    public function actionFetch()
    {
        $api_name = Api::format($_SERVER['REQUEST_URI']);
        $api = Api::model()->findByName($api_name);
        if (is_null($api)) {
            throw new CException("api($api_name) is not available");
        }
        if (is_null($api->result)) {
            throw new CException("result is not available for api($api_name)");
        }

        header("Content-Type: application/json; charset=utf-8");
        header("Access-Control-Allow-Origin: *");
        echo $api->result->result_content;
        Yii::app()->end();
    }
}
