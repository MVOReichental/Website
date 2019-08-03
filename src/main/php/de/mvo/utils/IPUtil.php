<?php
namespace de\mvo\utils;

class IPUtil
{
    public static function getClientIP($trustedProxies = [])
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) and in_array($_SERVER["REMOTE_ADDR"], $trustedProxies)) {
            $ips = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
            return trim(end($ips));
        }

        return $_SERVER["REMOTE_ADDR"];
    }
}