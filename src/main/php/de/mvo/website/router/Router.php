<?php
namespace de\mvo\website\router;

use AltoRouter;
use de\mvo\website\router\target\Page;
use de\mvo\website\router\target\Target;
use de\mvo\website\utils\HttpMethod;

class Router extends AltoRouter
{
	public function mapAll($routes)
	{
		/**
		 * @var $route Route
		 */
		foreach ($routes as $route)
		{
			$this->map($route->method, $route->route, $route->target);
		}
	}

	public function execute()
	{
		$method = $_SERVER["REQUEST_METHOD"];

		$match = parent::match(null, $method);
		if ($match === false)
		{
			self::route(new Page("NotFound"), HttpMethod::GET);
			return;
		}

		self::route($match["target"], $method, $match["params"]);
	}

	public static function route(Target $target, $method, $parameters = array())
	{
		$target->execute($method, $parameters);
	}
}