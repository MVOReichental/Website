<?php
namespace de\mvo\router;

class Target
{
	public $class;
	public $method;

	public function __construct($class, $method)
	{
		$this->class = basename($class);
		$this->method = $method;
	}
}