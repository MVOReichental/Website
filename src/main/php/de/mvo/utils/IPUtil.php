<?php
namespace de\mvo\utils;

class IPUtil
{
    public static function getClientIP($trustedProxies = [])
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            foreach ($trustedProxies as $trustedProxy) {
                if (self::isIPInRange($_SERVER["REMOTE_ADDR"], $trustedProxy)) {
                    $ips = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
                    return trim(end($ips));
                }
            }
        }

        return $_SERVER["REMOTE_ADDR"];
    }

    // Taken from https://gist.github.com/tott/7684443
    public static function isIPInRange($ip, $range)
    {
        if (strpos($range, "/") === false) {
            $range .= "/32";
        }

        // $range is in IP/CIDR format eg 127.0.0.1/24
        list($range, $netmask) = explode("/", $range, 2);
        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
        $netmaskDecimal = ~$wildcardDecimal;

        return (($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal));
    }
}