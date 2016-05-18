<?php
namespace de\mvo\router;

class Endpoint
{
	/**
	 * @var string
	 */
	public $method;
	/**
	 * @var string
	 */
	public $path;
	/**
	 * @var Target
	 */
	public $target;

	public function __construct($method, $path, Target $target)
	{
		$this->method = $method;
		$this->path = $path;
		$this->target = $target;
	}
}