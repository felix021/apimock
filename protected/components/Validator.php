<?php

/**
 * 验证器
 */
class Validator
{
    public static function isIdNum($id_num)
    {
        $idcard = strtoupper($id_num);
        // 只能是18位
        if (strlen($idcard) != 18) {
            return false;
        }

        //省份列表
        $province_list = array(
            '11', '12', '13', '14', '15', '21', '22',
            '23', '31', '32', '33', '34', '35', '36',
            '37', '41', '42', '43', '44', '45', '46',
            '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91'
        );
        if (!in_array(substr($idcard, 0, 2), $province_list)) {
            return false;
        }

        // 取出本体码
        $idcard_base = substr($idcard, 0, 17);

        // 取出校验码
        $verify_code = substr($idcard, 17, 1);

        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        // 校验码对应值
        $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        // 根据前17位计算校验码
        $total = 0;
        for ($i = 0; $i < 17; $i++) {
            $total += substr($idcard_base, $i, 1) * $factor[$i];
        }

        // 取模
        $mod = $total % 11;

        // 比较校验码
        return ($verify_code == $verify_code_list[$mod]);
    }

    public static function isNonEmptyString($key)
    {
        return is_string($key) and ! empty($key);
    }

    public static function isDate($date)
    {
        return strptime($date, '%Y-%m-%d') !== false;
    }

    //万分之一
    public static function isRate($rate)
    {
        return is_numeric($rate) and $rate > 0;
    }

    public static function isInteger($amount)
    {
        return is_numeric($amount) and strval(intval($amount)) === $amount;
    }

    public static function isDateTime($datetime)
    {
        return strptime($datetime, '%Y-%m-%d %H:%M:%S') !== false;
    }

    public static function isHttpUrl($url)
    {
        return preg_match('@^https?://(-\.)?([^\s/?\.#-]+\.?)+(/[^\s]*)?$@iS', $url);
    }

    public static function isJson($json)
    {
        return trim($json) == 'null' or json_decode($json, true) !== null;
    }
}
