<?php

//mh

class Mh
{
    public static function y2f($yuan)
    {
        return intval($yuan * 100 + 0.1);
    }

    public static function f2y($fen)
    {
        return sprintf("%.2f", $fen / 100);
    }
}
