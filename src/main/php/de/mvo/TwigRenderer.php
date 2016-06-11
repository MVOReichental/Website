<?php
namespace de\mvo;

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
		if (self::$twig !== null)
		{
			return;
		}

		$loader = new Twig_Loader_Filesystem(VIEWS_ROOT);

		self::$twig = new Twig_Environment($loader);

		$cache = Config::getInstance()->getValue("twig", "cache");
		if ($cache !== null)
		{
			self::$twig->setCache($cache);
		}
	}

	public static function render($name, $context = array())
	{
		return self::$twig->render($name . ".twig", $context);
	}
}