<?php
namespace de\mvo;

use de\mvo\model\users\User;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigRenderer
{
    /**
     * @var Environment
     */
    public static $twig;

    public static function init()
    {
        if (self::$twig !== null) {
            return;
        }

        if (isset($_SERVER["PATH_INFO"])) {
            $path = $_SERVER["PATH_INFO"];
        } else {
            $path = "";
        }

        $loader = new FilesystemLoader(VIEWS_ROOT);

        self::$twig = new Environment($loader);

        self::$twig->addGlobal("currentYear", date("Y"));
        self::$twig->addGlobal("currentFormattedDate", date("d.m.Y"));
        self::$twig->addGlobal("currentUser", User::getCurrent());
        self::$twig->addGlobal("internal", (substr(ltrim($path, "/"), 0, 8) == "internal" and User::getCurrent()));
        self::$twig->addGlobal("path", $path);
        self::$twig->addGlobal("hasOriginUser", isset($_SESSION["originUserId"]));

        self::$twig->addFunction(new TwigFunction("asset", function (string $path) {
            if (APP_VERSION === null) {
                return $path;
            }

            return sprintf("%s?v=%s", $path, APP_VERSION);
        }));

        self::$twig->addFunction(new TwigFunction("isActivePage", function (string $url) use ($path) {
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
     * @throws Error
     */
    public static function render($name, $context = array())
    {
        return self::$twig->render($name . ".twig", $context);
    }
}