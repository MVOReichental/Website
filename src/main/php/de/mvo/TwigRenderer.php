<?php
namespace de\mvo;

use de\mvo\model\users\User;
use Symfony\Component\Asset\Package;
use Twig_Environment;
use Twig_Error;
use Twig_Function;
use Twig_Loader_Filesystem;

class TwigRenderer
{
    /**
     * @var Twig_Environment
     */
    public static $twig;

    public static function init(Package $assetsPackage)
    {
        if (self::$twig !== null) {
            return;
        }

        if (isset($_SERVER["PATH_INFO"])) {
            $path = $_SERVER["PATH_INFO"];
        } else {
            $path = "";
        }

        $loader = new Twig_Loader_Filesystem(VIEWS_ROOT);

        self::$twig = new Twig_Environment($loader);

        $isInternal = (substr(ltrim($path, "/"), 0, 8) == "internal" and User::getCurrent());

        self::$twig->addGlobal("currentYear", date("Y"));
        self::$twig->addGlobal("currentFormattedDate", date("d.m.Y"));
        self::$twig->addGlobal("currentUser", User::getCurrent());
        self::$twig->addGlobal("internal", $isInternal);
        self::$twig->addGlobal("path", $path);
        self::$twig->addGlobal("hasOriginUser", isset($_SESSION["originUserId"]));
        self::$twig->addGlobal("assetPrefix", $isInternal ? "main-internal" : "main-public");

        self::$twig->addFunction(new Twig_Function("asset", function (string $path) use ($assetsPackage) {
            return $assetsPackage->getUrl($path);
        }));

        self::$twig->addFunction(new Twig_Function("isActivePage", function (string $url) use ($path) {
            $urlParts = explode("/", trim($url, "/"));
            $pathParts = explode("/", trim($path, "/"));

            foreach ($urlParts as $index => $part) {
                if (!isset($pathParts[$index])) {
                    return false;
                }

                if ($pathParts[$index] !== $part) {
                    return false;
                }
            }

            return true;
        }));

        if (Config::getValue("twig", "cache", false)) {
            self::$twig->setCache(TWIG_CACHE_ROOT);
        }
    }

    /**
     * @param $name
     * @param array $context
     * @return string
     * @throws Twig_Error
     */
    public static function render($name, $context = array())
    {
        return self::$twig->render($name . ".twig", $context);
    }
}