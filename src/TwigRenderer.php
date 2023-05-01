<?php
namespace App;

use App\Entity\users\User;
use Symfony\Component\Asset\Package;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

use const App\TWIG_CACHE_ROOT;
use const App\VIEWS_ROOT;

class TwigRenderer
{
    /**
     * @var Environment
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

        $loader = new FilesystemLoader(VIEWS_ROOT);

        self::$twig = new Environment($loader);

        $isInternal = (substr(ltrim($path, "/"), 0, 8) == "internal" and User::getCurrent());

        self::$twig->addGlobal("currentYear", date("Y"));
        self::$twig->addGlobal("currentFormattedDate", date("d.m.Y"));
        self::$twig->addGlobal("currentUser", User::getCurrent());
        self::$twig->addGlobal("internal", $isInternal);
        self::$twig->addGlobal("path", $path);
        self::$twig->addGlobal("hasOriginUser", isset($_SESSION["originUserId"]));
        self::$twig->addGlobal("assetPrefix", $isInternal ? "main-internal" : "main-public");

        self::$twig->addFunction(new TwigFunction("asset", function (string $path) use ($assetsPackage) {
            return $assetsPackage->getUrl($path);
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