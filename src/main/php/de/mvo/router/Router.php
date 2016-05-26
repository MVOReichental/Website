<?php
namespace de\mvo\router;

use AltoRouter;
use de\mvo\renderer\Renderer;

class Router extends AltoRouter
{
	public function map($method, $route, Renderer $renderer)
	{
		parent::map($method, $route, $renderer);
	}

	public function mapAll($endpoints)
	{
		/**
		 * @var $endpoint Endpoint
		 */
		foreach ($endpoints as $endpoint)
		{
			$this->map($endpoint->method, $endpoint->path, $endpoint->renderer);
		}
	}

	public function match($requestUrl = null, $requestMethod = null)
	{
		$match = parent::match($requestUrl, $requestMethod);
		if ($match === false)
		{
			return null;
		}

		return new Match($match["params"], $match["target"]);
	}
}