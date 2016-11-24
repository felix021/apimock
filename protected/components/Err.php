<?php

/* 错误码、错误信息 */

class Err extends CErrorHandler
{
    const E_SUCCESS         = 0;
    const E_FAIL            = 1;
    const E_PROCESSING      = 2;
    const E_INVALID_REQUEST = 3;
    const E_REPEAT_REQUEST  = 4;
    const E_INTERNAL        = 1000;

    private static $error_message = [
        self::E_SUCCESS         => 'success',
        self::E_FAIL            => 'failed',
        self::E_PROCESSING      => 'processing',
        self::E_INVALID_REQUEST => '请求不存在',
        self::E_INTERNAL        => 'internal error',
    ];

    const DB_DUPLICATED     = 23000; #MySQL Duplicated Key

    public static function strerror($errno)
    {
        if (array_key_exists($errno, self::$error_message)) {
            return self::$error_message[$errno];
        }
        return 'unknown error';
    }

    public static function outputJson($errno, $message, $data)
    {
        if (is_null($message)) {
            $message = self::strerror($errno);
        }
        header("Content-type: application/json; charset=utf-8");
        echo json_encode([
            'code'      => $errno,
            'message'   => $message,
            'data'      => $data
        ], JSON_UNESCAPED_UNICODE);
    }

    public static function outputError($errno, $message = null, $data = null)
    {
        if (method_exists(Yii::app()->controller, 'outputError')) {
            Yii::app()->controller->outputError($errno, $message, $data);
        } else {
            self::outputJson($errno, $message, $data);
        }
        Yii::app()->end();
    }

    public function handleException($exc)
    {
        $code = self::E_INTERNAL;
        $message = sprintf('[%s] %s: %s ', get_class($exc), $exc->getCode(), $exc->getMessage());
        $detail = sprintf("raised on line(%d) in %s", $exc->getLine(), $exc->getFile());
        self::outputError($code, $message, $detail);
    }

    public function handleError($event)
    {
        $code = self::E_INTERNAL;
        $message = sprintf("Error: [%s] %s\n", $event->code, $event->message);
        $detail = sprintf("raised on line(%d) in %s", $event->line, $event->file);
        self::outputError($code, $message, $detail);
    }
}
