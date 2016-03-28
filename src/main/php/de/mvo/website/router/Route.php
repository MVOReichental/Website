<?php
namespace de\mvo\website\router;

class Route
{
	public $method;
	public $route;
	public $target;

	public function __construct($method, $route, $target)
	{
		$this->method = $method;
		$this->route = $route;
		$this->target = $target;
	}
}