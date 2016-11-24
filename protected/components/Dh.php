<?php

//fengmin

class Dh
{
    public static function now($format = 'Y-m-d H:i:s')
    {
        return date($format);
    }

    public static function microtime()
    {
        return microtime(true);
    }

    public static function ago($seconds, $format = 'Y-m-d H:i:s')
    {
        return date($format, time() - $seconds);
    }
}
