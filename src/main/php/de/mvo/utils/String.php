<?php
namespace de\mvo\utils;

class String
{
    public static function removeNonAlphanumeric($string)
    {
        return preg_replace("/[^A-Za-z0-9]/", "", $string);
    }
}