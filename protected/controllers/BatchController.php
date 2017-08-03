<?php

class BatchController extends CController
{
    public function actionIndex($batch_name)
    {
        $batch = Batch::model()->findByName($batch_name);
        if (!$batch) {
            throw new CException("batch($batch_name) is not available");
        }

        $prefix = sprintf('/batch/%s', $batch_name);
        $api_name = Api::format(str_replace($prefix, '', $_SERVER['REQUEST_URI']));

        $api = Api::model()->with('result')->findByName($api_name);
        if (is_null($api)) {
            throw new CException("api($api_name) is not available");
        }

        $result = null;

        $result_id = Rule::model()->findResult($batch->batch_id, $api->api_id);
        if (!is_null($result_id)) {
            $result = Result::model()->findByPk($result_id);
            if (is_null($result)) {
                throw new CException("result($result_id) is not available for api($api_name), deleted?");
            }
        } else {
            if (is_null($api->result)) {
                throw new CException("result is not available for api($api_name), please choose one result in apieditor");
            }
            $result = $api->result;
        }

        header("Content-Type: application/json; charset=utf-8");
        header("Access-Control-Allow-Origin: *");
        echo $result->result_content;
        Yii::app()->end();
    }
}
