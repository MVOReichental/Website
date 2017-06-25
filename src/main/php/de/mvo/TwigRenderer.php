<?php
namespace de\mvo;

use de\mvo\model\users\User;
use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigRenderer
{
    /**
     * @var Twig_Environment
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

        $loader = new Twig_Loader_Filesystem(VIEWS_ROOT);

        self::$twig = new Twig_Environment($loader);

        self::$twig->addGlobal("currentYear", date("Y"));
        self::$twig->addGlobal("currentFormattedDate", date("d.m.Y"));
        self::$twig->addGlobal("currentUser", User::getCurrent());
        self::$twig->addGlobal("internal", (substr(ltrim($path, "/"), 0, 8) == "internal" and User::getCurrent()));
        self::$twig->addGlobal("path", $path);

        if (Config::getValue("twig", "cache", false)) {
            self::$twig->setCache(RESOURCES_ROOT . "/twig-cache");
        }
    }

    public static function render($name, $context = array())
    {
        return self::$twig->render($name . ".twig", $context);
    }
}