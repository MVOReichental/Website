<?php
namespace de\mvo\utils;

class Url
{
    public static function getBaseUrl()
    {
        $baseUrl = getenv("APP_BASE_URL");
        if ($baseUrl !== false) {
            return $baseUrl;
        }

        if (isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"]) {
            $scheme = "https";
            $defaultPort = 443;
        } else {
            $scheme = "http";
            $defaultPort = 80;
        }

        if ($_SERVER["SERVER_PORT"] == $defaultPort) {
            $port = null;
        } else {
            $port = $_SERVER["SERVER_PORT"];
        }

        return $scheme . "://" . $_SERVER["SERVER_NAME"] . ($port ? (":" . $port) : "");
    }
}