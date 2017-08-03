<?php

class ApieditorController extends Controller
{
    protected $validate_map = [
        'api_id'            => ['Validator', 'isInteger'],
        'api_name'          => ['Validator', 'isNonEmptyString'],
        'api_desc'          => ['Validator', 'isNonEmptyString'],
        'result_id'         => ['Validator', 'isInteger'],
        'result_desc'       => ['Validator', 'isNonEmptyString'],
        'result_content'    => null,
        'batch_id'          => ['Validator', 'isInteger'],
        'batch_name'        => ['Validator', 'isNonEmptyString'],
        'batch_desc'        => null,
        'rule_id'           => ['Validator', 'isInteger'],
        'json'              => ['Validator', 'isJson'],
    ];

    public function actionTest()
    {
        $this->render('test');
    }

    public function actionIndex()
    {
        $this->render("index");
    }

    public function actionList()
    {
        $key = Yii::app()->request->getParam('key', '');
        if ($key) {
            $arr_api = Api::model()->search(trim($key));
        } else {
            $arr_api = Api::model()->findAll();
        }
        $api_list = [];
        foreach ($arr_api as $api) {
            $api_list[] = [
                'api_id'    => $api->api_id,
                'api_name'  => $api->api_name,
                'api_desc'  => $api->api_desc,
            ];
        }
        $this->ajaxOutput(Err::E_SUCCESS, '', ['api_list' => $api_list]);
    }

    public function actionDetail()
    {
        $api_name = Yii::app()->request->getParam('api_name', '');
        if (!$api_name) {
            throw new CException("invalid request");
        }
        $api_name = Api::format($api_name);
        $api = Api::model()->with('resultSet')->findByName($api_name);
        if (!$api) {
            throw new CException("invalid api: $api_name");
        }

        $data = [
            'api_id'   => $api->api_id,
            'api_name' => $api->api_name,
            'api_desc' => $api->api_desc,
            'result_set'   => [],
        ];
        foreach ($api->resultSet as $result) {
            $data['result_set'][] = [
                'chosen' => $result->result_id == $api->api_result_id ? 1 : 0,
                'result_id' => $result->result_id,
                'result_desc' => $result->result_desc,
                'result_content' => $result->result_content,
            ];
        }
        $this->ajaxOutput(Err::E_SUCCESS, '', $data);
    }

    public function actionResult()
    {
        $result_id = Yii::app()->request->getParam('result_id', '');
        if (!$result_id) {
            throw new CException("invalid request");
        }
        $result = Result::model()->findByPk($result_id);
        if (!$result) {
            throw new CException("invalid result_id: $result_id");
        }
        $this->ajaxOutput(Err::E_SUCCESS, '', [
            'result_desc' => $result->result_desc,
            'result_content' => $result->result_content,
        ]);
    }

    public function actionChoose()
    {
        $d = $this->buildData(['api_id', 'result_id']);
        $api = Api::model()->findByPk($d['api_id']);
        if (!$api) {
            throw new CException("invalid api_id: {$d['api_id']}");
        }
        $api->changeResult($d['result_id']);
        $this->ajaxOutput(Err::E_SUCCESS, '');
    }

    public function actionAddResult()
    {
        $d = $this->buildData(['api_id', 'result_desc', 'result_content']);
        $result = Result::model()->create($d['api_id'], $d['result_desc'], $d['result_content']);
        $this->ajaxOutput(Err::E_SUCCESS, '', ['result_id' => $result->result_id]);
    }

    public function actionSaveResult()
    {
        $d = $this->buildData(['api_id', 'result_id', 'result_desc', 'result_content']);
        if ($d['result_id'] == 0) {
            $result = Result::model()->create($d['api_id'], $d['result_desc'], $d['result_content']);
            if ($result->api->api_result_id == 0) {
                $result->api->changeResult($result->result_id);
            }
        } else {
            $result = Result::model()->findByPk($d['result_id']);
            if (!$result) {
                throw new CException("invalid result_id: {$d['result_id']}");
            }
            $result->modify($d['result_desc'], $d['result_content']);
        }
        $this->ajaxOutput(Err::E_SUCCESS, '', ['result_id' => $result->result_id]);
    }

    public function actionRemoveResult()
    {
        $d = $this->buildData(['result_id']);
        if (!Result::model()->deleteByPk($d['result_id'])) {
            $this->ajaxOutput(Err::E_FAIL, var_export(Result::model()->getErrors(), true));
        } else {
            $this->ajaxOutput(Err::E_SUCCESS, '');
        }
    }

    public function actionChangeApiDesc()
    {
        $d = $this->buildData(['api_id', 'api_desc']);
        $api = Api::model()->findByPk($d['api_id']);
        if (!$api) {
            throw new CException("invalid api_id");
        }
        $api->changeDesc($d['api_desc']);
        $this->ajaxOutput(Err::E_SUCCESS, '');
    }

    public function actionRemoveApi()
    {
        $d = $this->buildData(['api_id']);
        $api = Api::model()->findByPk($d['api_id']);
        if (!$api) {
            throw new CException("invalid api_id");
        }
        if ($api->resultSet) {
            $this->ajaxOutput(Err::E_FAIL, '请先删除所有返回结果');
        }
        Api::model()->deleteByPk($d['api_id']);
        $this->ajaxOutput(Err::E_SUCCESS, '');
    }

    public function actionAddApi()
    {
        $d = $this->buildData(['api_name']);
        $api = Api::model()->create($d['api_name'], '');
        $this->ajaxOutput(Err::E_SUCCESS, '');
    }

    public function actionFormatJson()
    {
        $d = $this->buildData(['json']);
        $obj = json_decode($d['json'], false);
        $this->ajaxOutput(Err::E_SUCCESS, '', [
            'json' => json_encode($obj, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ]);
    }

    public function actionBatch()
    {
        $this->render("batch");
    }

    public function actionBatchList()
    {
        $key = Yii::app()->request->getParam('key', '');
        if ($key) {
            $arr_batch = Batch::model()->search($key);
        } else {
            $arr_batch = Batch::model()->findAll();
        }
        $batch_list = [];
        foreach ($arr_batch as $batch) {
            $batch_list[] = [
                'batch_id'    => $batch->batch_id,
                'batch_name'  => $batch->batch_name,
                'batch_desc'  => $batch->batch_desc,
            ];
        }
        $this->ajaxOutput(Err::E_SUCCESS, '', ['batch_list' => $batch_list]);
    }

    public function actionBatchDetail()
    {
        $batch_id = Yii::app()->request->getParam('batch_id', '');
        $batch = Batch::model()->with('ruleSet')->findByPk($batch_id);
        if (!$batch) {
            throw new CException("invalid batch_id");
        }
        $data = [
            'batch_id'   => $batch->batch_id,
            'batch_name' => $batch->batch_name,
            'batch_desc' => $batch->batch_desc,
            'rule_set'   => [],
        ];
        foreach ($batch->ruleSet as $rule) {
            $api = $rule->api;
            $result = $rule->result;
            $data['rule_set'][] = [
                'rule_id' => $rule->rule_id,
                'rule_name' => $rule->rule_name,
                'api_name' => $api->api_name,
                'api_desc' => $api->api_desc,
                'result_desc' => $result->result_desc,
                'result_content' => $result->result_content,
            ];
        }
        $this->ajaxOutput(Err::E_SUCCESS, '', $data);
    }

    public function actionAddBatch()
    {
        $d = $this->buildData(['batch_name']);
        $batch = Batch::model()->create($d['batch_name']);
        $this->ajaxOutput(Err::E_SUCCESS, '', ['batch_id' => $batch->batch_id]);
    }

    public function actionChangeBatchDesc()
    {
        $d = $this->buildData(['batch_id', 'batch_desc']);
        $batch = Batch::model()->findByPk($d['batch_id']);
        if (!$batch) {
            throw new CException("invalid batch_id");
        }
        $batch->changeDesc($d['batch_desc']);
        $this->ajaxOutput(Err::E_SUCCESS, '');
    }

    public function actionRemoveRule()
    {
        $d = $this->buildData(['rule_id']);
        if (!Rule::model()->deleteByPk($d['rule_id'])) {
            $this->ajaxOutput(Err::E_FAIL, var_export(Rule::model()->getErrors(), true));
        } else {
            $this->ajaxOutput(Err::E_SUCCESS, '');
        }
    }

    public function actionSaveRule()
    {
        $d = $this->buildData(['batch_id', 'api_name', 'result_id']);
        $api = Api::model()->findByName($d['api_name']);
        if (!$api) {
            throw new CException("invalid api: {$d['api_name']}");
        }
        $result = Result::model()->findByPk($d['result_id']);
        if (!$result) {
            throw new CException("invalid result: {$d['result_id']}");
        }
        if ($result->result_api_id != $api->api_id) {
            throw new CException("result not match api");
        }
        $rule = Rule::model()->saveRule($d['batch_id'], $api->api_id, $d['result_id']);
        $this->ajaxOutput(Err::E_SUCCESS, '', ['rule_id' => $rule->rule_id]);
    }

    public function actionApplyBatch()
    {
        $d = $this->buildData(['batch_id']);
        $batch = Batch::model()->with('ruleSet')->findByPk($d['batch_id']);
        if (!$batch) {
            throw new CException("invalid batch: {$d['batch_id']}");
        }
        $batch->apply();
        $this->ajaxOutput(Err::E_SUCCESS, '');
    }

    public function actionExport()
    {
        $arr_api = Api::model()->with('result')->findAll();
        $result = [];
        foreach ($arr_api as $api) {
            $result[$api->api_name] = json_decode($api->result->result_content, true);
        }
        header("Content-Type: application/json");
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
