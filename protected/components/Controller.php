<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
    protected $validate_map = [];
    protected $default_values = [];

    protected function validate($key, $value)
    {
        if (!array_key_exists($key, $this->validate_map)) {
            throw new CException("illegal key `$key` in validate_map");
        }
        $method = $this->validate_map[$key];
        if ($method and !call_user_func($method, $value)) {
            throw new CException("illegal value($value) of `$key`");
        }
    }

    protected function buildData($keys)
    {
        $data = [];
        foreach ($keys as $key) {
            $value = Yii::app()->request->getPost($key, null);
            if (is_null($value)) {
                if (array_key_exists($key, $this->default_values)) {
                    $value = $this->default_values[$key];
                }
            }
            $this->validate($key, $value);
            $data[$key] = $value;
        }
        return $data;
    }

    public function ajaxOutput($code, $message = null, $data = null)
    {
        Err::outputJson($code, $message, $data);
        Yii::app()->end();
    }
}
