<?php
namespace de\mvo\router;

use AltoRouter;

class Router extends AltoRouter
{
	public function map($method, $route, Target $target)
	{
		parent::map($method, $route, $target);
	}

	public function mapAll($endpoints)
	{
		/**
		 * @var $endpoint Endpoint
		 */
		foreach ($endpoints as $endpoint)
		{
			$this->map($endpoint->method, $endpoint->path, $endpoint->target);
		}
	}

	public function match($requestUrl = null, $requestMethod = null)
	{
		$match = parent::match($requestUrl, $requestMethod);
		if ($match === false)
		{
			return null;
		}

		return new Match((object) $match["params"], $match["target"]);
	}
}