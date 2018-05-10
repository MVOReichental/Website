<?php
namespace de\mvo\utils;

use Parsedown;

class StringUtil
{
    public static function removeNonAlphanumeric($string)
    {
        return preg_replace("/[^A-Za-z0-9]/", "", $string);
    }

    public static function format($text)
    {
        $parsedown = Parsedown::instance();

        $parsedown->setBreaksEnabled(true);
        $parsedown->setMarkupEscaped(true);

        $text = str_replace("javascript:", "javascript%3A", $text);// Escape JavaScript links (e.g. javascript:someFunction())

        return $parsedown->text($text);
    }
}